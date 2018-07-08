<?php

namespace Swagception;

use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriResolver;
use JsonSchema\Uri\UriRetriever;
use Swagception\Exception\ValidationException;

class SwaggerSchema
{
    /**
     * @var object The Swagger 2.0 json schema.
     */
    protected $schema;
    /**
     * @var string Scheme to access the API (eg. http, https)
     */
    protected $scheme;
    /**
     * @var string API host eg. your.api.com
     */
    protected $host;
    /**
     * @var string API base path eg. /api
     */
    protected $basePath;
    /**
     * @var \Swagception\PathHandlerLoader\LoadsPathHandlers
     */
    protected $pathHandlerLoader;
    /**
     * @var \Swagception\URLRetriever\CanRetrieveURLs
     */
    protected $urlRetriever;
    /**
     * @var string[] Only match routes containing at least one of these.
     */
    protected $filters;

    protected $convertedPaths;

    public function __construct()
    {
        $this->convertedPaths = [];
    }

    public static function Create()
    {
        return new static();
    }

    public function getTemplatePath($path, $method = 'get', $statusCode = 200)
    {
        if (!isset($this->convertedPaths[$method][$path][$statusCode])) {
            return $path;
        }

        return $this->convertedPaths[$method][$path][$statusCode];
    }

    public function convertPath($templatePath, $method = 'get', $statusCode)
    {
        $handlesPath = $this->getPathHandlerLoader()->getHandler($templatePath);
        $actualPath  = $handlesPath->convertPath($templatePath, $method, $statusCode);

        $this->convertedPaths[$method][$actualPath][$statusCode] = $templatePath;
        $this->getPathHandlerLoader()->unloadHandlers();
        return $actualPath;
    }

    /**
     * @param string $path
     * @param string $method
     * @param int    $expectedStatusCode
     *
     * @throws \Swagception\Exception\ValidationException
     */
    public function testPath($path, $method = 'get', $expectedStatusCode = 200)
    {
        //Check whether it's a template path or one which has been previously converted into an actual path.
        if (!isset($this->convertedPaths[$method][$path][$expectedStatusCode])) {
            $actualPath   = $this->convertPath($path, $method, $expectedStatusCode);
            $templatePath = $path;
        } else {
            $actualPath   = $path;
            $templatePath = $this->convertedPaths[$method][$actualPath][$expectedStatusCode];
        }

        $response = $this->getURLRetriever()->request($this->getURL() . $actualPath, $method);
        if ($response->getStatusCode() !== $expectedStatusCode) {
            throw new ValidationException('Status Code: %d, expected %d', $response->getStatusCode(), $expectedStatusCode);
        }
        if (isset($this->schema->paths->$templatePath->$method->responses->$expectedStatusCode->schema)) {
            $json = json_decode($response->getBody()->getContents());
            (new Validator\Validator())
                ->validate($this->schema->paths->$templatePath->$method->responses->$expectedStatusCode->schema, $json);
        }
    }

    /**
     * Checks whether the path should be included, based on the previously specified filters.
     *
     * @param string $path
     *
     * @return bool
     */
    public function checkFilter($path)
    {
        //No filters? Include all paths.
        if (empty($this->filters)) {
            return true;
        }

        //Match either with or without braces.
        $cleanPath = str_replace(['{', '}'], ['', ''], $path);
        foreach ($this->filters as $filter) {
            if (strpos($path, $filter) !== false || strpos($cleanPath, $filter) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param mixed $key Schema object key
     *
     * @return mixed Schema object value
     */
    public function __get($key)
    {
        return $this->schema->$key;
    }

    public function getURL()
    {
        return $this->getScheme() . '://' . $this->getHost() . $this->getBasePath();
    }

    public function getPaths()
    {
        $pathList = [];
        foreach ($this->schema->paths as $path => $pathData) {
            if ($this->checkFilter($path)) {
                foreach (array_keys(get_object_vars($pathData)) as $action) {
                    $statusCodes = array_keys(get_object_vars($pathData->{$action}->responses));
                    $statusCode  = array_shift($statusCodes);
                    $pathList[]  = [
                        'method' => $action,
                        'path'   => $this->convertPath($path, $action, $statusCode),
                        'code'   => $statusCode,
                    ];
                }
            }
        }
        return $pathList;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        if (!isset($this->scheme)) {
            $this->loadDefaultScheme();
        }
        return $this->scheme;
    }

    /**
     * Specify your own scheme.
     *
     * @param string $scheme
     *
     * @return static
     */
    public function withScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }

    public function loadDefaultScheme()
    {
        return $this->withScheme(isset($this->schema->schemes) ? $this->schema->schemes[0] : 'https');
    }

    /**
     * @return string
     */
    public function getHost()
    {
        if (!isset($this->host)) {
            $this->loadDefaultHost();
        }
        return $this->host;
    }

    /**
     * Specify your own host.
     *
     * @param string $host
     *
     * @return static
     */
    public function withHost($host)
    {
        $this->host = $host;
        return $this;
    }

    public function loadDefaultHost()
    {
        if (!isset($this->schema->host)) {
            throw new \Exception('Host must be specified, either in the schema or by calling withHost');
        }

        return $this->withHost($this->schema->host);
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        if (!isset($this->basePath)) {
            $this->loadDefaultBasePath();
        }
        return $this->basePath;
    }

    /**
     * Specify your own basePath.
     *
     * @param string $basePath
     *
     * @return static
     */
    public function withBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    public function loadDefaultBasePath()
    {
        if (!isset($this->schema->basePath)) {
            throw new \Exception('BasePath must be specified, either in the schema or by calling withBasePath');
        }

        return $this->withBasePath($this->schema->basePath);
    }

    /**
     * Allow the system to generate the schema.
     *
     * @param string $specURI
     *
     * @return static
     */
    public function withSchemaURI($specURI)
    {
        return $this->withSchema($this->loadSchemaFromURI($specURI));
    }

    /**
     * @return object
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Specify your own schema.
     *
     * @param object $schema
     *
     * @return static
     */
    public function withSchema($schema)
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * @param string $specURI
     */
    protected function loadSchemaFromURI($specURI)
    {
        $refResolver = new SchemaStorage(new UriRetriever(), new UriResolver());
        return $refResolver->resolveRef($specURI);
    }

    public function getURLRetriever()
    {
        if (!isset($this->urlRetriever)) {
            $this->loadDefaultURLRetriever();
        }
        return $this->urlRetriever;
    }

    public function withURLRetriever(\Swagception\URLRetriever\CanRetrieveURLs $urlRetriever)
    {
        $this->urlRetriever = $urlRetriever;
        return $this;
    }

    protected function loadDefaultURLRetriever()
    {
        $this->urlRetriever = new \Swagception\URLRetriever\URLRetriever();
    }

    public function getPathHandlerLoader()
    {
        if (!isset($this->pathHandlerLoader)) {
            $this->loadDefaultPathHandlerLoader();
        }
        return $this->pathHandlerLoader;
    }

    public function withPathHandlerLoader(\Swagception\PathHandlerLoader\LoadsPathHandlers $pathHandlerLoader)
    {
        $this->pathHandlerLoader = $pathHandlerLoader;
        return $this;
    }

    protected function loadDefaultPathHandlerLoader()
    {
        $this->pathHandlerLoader = new \Swagception\PathHandlerLoader\PathHandlerLoader($this);
    }

    public function withFilters($filters)
    {
        $this->filters = $filters;
        return $this;
    }
}
