<?php
namespace Swagception;

use JsonSchema\RefResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Uri\UriResolver;

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
     * @var \Swagception\PathHandler\PathHandlerLoader
     */
    protected $pathHandlerLoader;
    /**
     * @var \Swagception\URLRetriever\CanRetrieveURLs
     */
    protected $urlRetriever;
    /**
     * @var bool Whether we use the default path handler (which fetches enum and x-example for parameters) or whether we're always going to use our own path handler classes.
     */
    protected $useDefaultPathHandler;

    protected $applyToPathHandlers;

    protected $convertedPaths;

    public function __construct()
    {
        $this->useDefaultPathHandler = true;
        $this->applyToPathHandlers = [];
        $this->convertedPaths = [];
    }

    public static function Create()
    {
        return new static();
    }

    public function getTemplatePath($path)
    {
        if (!isset($this->convertedPaths[$path])) {
            return $path;
        } else {
            return $this->convertedPaths[$path];
        }
    }

    public function convertPath($templatePath)
    {
        $HandlesPath = $this->getHandlesPath($templatePath);
        $actualPath = $HandlesPath->convertPath($templatePath);
        $this->convertedPaths[$actualPath] = $templatePath;
        return $actualPath;
    }

    public function testPath($codeceptionActor, $path, $method = 'get', $expectedStatusCode = 200)
    {
        //Check whether it's a template path or one which has been previously converted into an actual path.
        if (!isset($this->convertedPaths[$path])) {
            $actualPath = $this->convertPath($path);
            $templatePath = $path;
        } else {
            $actualPath = $path;
            $templatePath = $this->convertedPaths[$actualPath];
        }

        try {
            $json = $this->getURLRetriever()->request($this->getURL() . $actualPath);

            (new Validator\Validator())
                ->validate($this->schema->paths->$templatePath->$method->responses->$expectedStatusCode->schema, $json);
        } catch (\Swagception\Exception\ValidationException $e) {
            $codeceptionActor->fail($e->getMessage());
        }
    }

    protected function getHandlesPath($path)
    {
        $HandlesPath = $this->getPathHandlerLoader()->getClass($path);

        if (empty($HandlesPath)) {
            if (!$this->useDefaultPathHandler) {
                throw new \Exception(sprintf('There is no path handler configured for path %1$s', $path));
            }

            //Use the default implementation.
            $HandlesPath = \Swagception\PathHandler\DefaultPathHandler::class;
        }

        $PathHandler = (new $HandlesPath())
            ->setSchema($this->schema);

        foreach ($this->applyToPathHandlers as $closure) {
            $closure($PathHandler);
        }

        return $PathHandler;
    }

    public function applyToPathHandlers($closure) {
        $this->applyToPathHandlers[] = $closure;
        return $this;
    }

    /**
     * @param mixed $key Schema object key
     * @return mixed Schema object value
     */
    public function __get($key)
    {
//        Pretend that this instance is the schema object itself.
//        return SwaggerSchema::Create()
//            ->withSchema($this->schema->$key);
        return $this->schema->$key;
    }

    public function getURL()
    {
        return $this->getScheme() . '://' . $this->getHost() . $this->getBasePath();
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
            throw new \Exception('Host must be specified, either in the schema or in this object');
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
            throw new \Exception('BasePath must be specified, either in the schema or in this object');
        }

        return $this->withBasePath($this->schema->basePath);
    }

    /**
     * Allow the system to generate the schema.
     *
     * @param string $specURI
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
     * @return static
     */
    public function withSchema($schema)
    {
        $this->schema = $schema;
        return $this;
    }

    public function useDefaultPathHandler($useDefaultPathHandler)
    {
        $this->useDefaultPathHandler = $useDefaultPathHandler;
        return $this;
    }

    /**
     * @param string $specURI
     */
    protected function loadSchemaFromURI($specURI)
    {
        $refResolver = new RefResolver(new UriRetriever(), new UriResolver());
        return $refResolver->resolve($specURI);
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

    public function withPathHandlerLoader(\Swagception\PathHandler\PathHandlerLoader $pathHandlerLoader)
    {
        $this->pathHandlerLoader = $pathHandlerLoader;
        return $this;
    }

    protected function loadDefaultPathHandlerLoader()
    {
        $this->pathHandlerLoader = new \Swagception\PathHandler\PathHandlerLoader();
    }
}