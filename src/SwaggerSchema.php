<?php
namespace Swagception;

use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Uri\UriResolver;

class SwaggerSchema implements Reporter\ReportsTests
{
    /**
     * @var Container\ContainsInstances
     */
    protected $container;
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
     * @var string[] Only match routes containing at least one of these.
     */
    protected $filters;
    /**
     * @var Reporter\ReportsTests[]
     */
    protected $reporters;
    /**
     * @var [string => string] Mapping of actual paths back to template paths.
     */
    protected $convertedPaths;
    /**
     * @var bool Whether or not finalise has been called.
     */
    protected $isFinalised;
    /**
     * @var bool Whether or not to generate an exception if the response was empty. Default is false, because empty responses are valid.
     */
    protected $errorOnEmpty;

    public function __construct(Container\ContainsInstances $container)
    {
        $this->withContainer($container);
        $this->convertedPaths = [];
        $this->reporters = [];
        $this->isFinalised = false;
        $this->errorOnEmpty = false;
    }

    /**
     * @return Container\ContainsInstances
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param Container\ContainsInstances $container
     * @return static
     */
    public function withContainer(Container\ContainsInstances $container)
    {
        $this->container = $container;
        return $this;
    }

    public static function Create(Container\ContainsInstances $container)
    {
        return new static($container);
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
        $HandlesPath = $this->getContainer()->getPathHandlerLoader()->getHandler($templatePath);
        $actualPath = $HandlesPath->convertPath($templatePath);
        $this->convertedPaths[$actualPath] = $templatePath;
        return $actualPath;
    }

    public function getConvertedPath($path, $convertIt = false)
    {
        if (isset($this->convertedPaths[$path])) {
            //Is the converted path.
            return $path;
        }

        $key = array_search($path, $this->convertedPaths, true);
        if ($key !== false) {
            //$path is the template path, so return the converted path
            return $key;
        }

        if ($convertIt) {
            //We don't have it... yet.
            return $this->convertPath($path);
        }

        //We don't have it.
        return null;
    }

    /**
     * @param string $path
     * @param string $method
     * @param int $expectedStatusCode
     * @throws \Swagception\Exception\ValidationException
     */
    public function testPath($path, $method = 'get', $expectedStatusCode = 200)
    {
        //Check whether it's a template path or one which has been previously converted into an actual path.
        $actualPath = $this->getConvertedPath($path, true);
        $templatePath = $this->getTemplatePath($path);
        $this->logDetail('Path', $templatePath);

        $uri = $this->getBaseURL() . $actualPath;
        $this->logDetail('URL', $uri);

        $json = $this->runRequest($uri, $method);
        $this->logDetail('Response', $json);

        if (empty($json) && $this->getErrorOnEmpty()) {
            throw new Exception\ResponseEmptyException(sprintf('URI %1$s returned no data.', $uri));
        }

        $this->getContainer()->getValidator()
            ->validate($this->schema->paths->$templatePath->$method->responses->$expectedStatusCode->schema, $json);
    }

    /**
     * Checks whether the path should be included, based on the previously specified filters.
     *
     * @param string $path
     * @return bool
     */
    public function checkFilter($path)
    {
        //No filters? Include all paths.
        if (empty($this->filters)) {
            return true;
        }

        //Match either with or without braces.
        $cleanPath = str_replace(array('{', '}'), array('', ''), $path);
        foreach ($this->filters as $filter) {
            if (stripos($path, $filter) !== false || stripos($cleanPath, $filter) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param mixed $key Schema object key
     * @return mixed Schema object value
     */
    public function __get($key)
    {
        return $this->schema->$key;
    }

    public function getBaseURL()
    {
        return $this->getScheme() . '://' . $this->getHost() . $this->getBasePath();
    }

    public function getPaths()
    {
        $pathList = [];
        foreach ($this->schema->paths as $path => $pathData) {
            if ($this->checkFilter($path)) {
                foreach (array_keys(get_object_vars($pathData)) as $method) {
                    //We only check get requests here.
                    if ($method !== 'get') {
                        continue;
                    }

                    $pathList[] = $path;
                }
            }
        }

        if (empty($pathList)) {
            if (empty($this->schema->paths)) {
                throw new Exception\ConfigurationException('The specified schema does not have any paths to test.');
            }
            $filters = !empty($this->filters) ? PHP_EOL . 'Filters:' . PHP_EOL . implode(PHP_EOL, $this->filters) : '';
            //$methods = !empty($this->methods) ? PHP_EOL . 'Methods:' . PHP_EOL . implode(PHP_EOL, $this->methods) : '';

            throw new Exception\ConfigurationException('Could not load any paths to test. Please check your filtering settings.' . $filters);
        }

        return $pathList;
    }

    public function convertPaths()
    {
        $pathList = [];
        foreach ($this->getPaths() as $path) {
            $pathList[] = $this->convertPath($path);
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

    /**
     * @param string $specURI
     */
    protected function loadSchemaFromURI($specURI)
    {
        $refResolver = new SchemaStorage(new UriRetriever(), new UriResolver());
        return $refResolver->resolveRef($specURI);
    }

    public function withFilters($filters)
    {
        $this->filters = $filters;
        return $this;
    }

    public function withAddedReporter(Reporter\ReportsTests $reporter)
    {
        $this->reporters[] = $reporter;
        return $this;
    }

    public function getReporters()
    {
        return $this->reporters;
    }

    public function getErrorOnEmpty()
    {
        return $this->errorOnEmpty;
    }

    public function withErrorOnEmpty($errorOnEmpty)
    {
        $this->errorOnEmpty = $errorOnEmpty;
        return $this;
    }

    public function logResult($result)
    {
        if (!empty($this->getReporters())) {
            foreach ($this->getReporters() as $Reporter) {
                $Reporter->logResult($result);
            }
        }
    }

    public function logDetail($header, $message)
    {
        if (!empty($this->getReporters())) {
            foreach ($this->getReporters() as $Reporter) {
                $Reporter->logDetail($header, $message);
            }
        }
    }

    public function setCurrentTest($test, $data = null)
    {
        if (!empty($this->getReporters())) {
            foreach ($this->getReporters() as $Reporter) {
                $Reporter->setCurrentTest($test, $data);
            }
        }
    }

    public function setCurrentTestName($name)
    {
        if (!empty($this->getReporters())) {
            foreach ($this->getReporters() as $Reporter) {
                $Reporter->setCurrentTestName($name);
            }
        }
    }

    public function setMiscInfo()
    {
        if (!empty($this->getReporters())) {
            foreach ($this->getReporters() as $Reporter) {
                $Reporter->setMiscInfo();
            }
        }
    }

    public function logException(\Exception $e)
    {
        if (!empty($this->getReporters())) {
            foreach ($this->getReporters() as $Reporter) {
                $Reporter->logException($e);
            }
        }
    }

    public function finalise()
    {
        if (!$this->isFinalised) {
            $this->setMiscInfo();

            //Unload handlers first, in case the unloading generates additional errors.
            $this->getContainer()->getHandlerContainer()->unload();

            if (!empty($this->getReporters())) {
                foreach ($this->getReporters() as $Reporter) {
                    $Reporter->finalise();
                }
            }
            $this->isFinalised = true;
        }
    }

    public function isFinalised()
    {
        return $this->isFinalised;
    }

    protected function runRequest($uri, $method)
    {
        $request = $this->getContainer()->getRequestGenerator()->generate($uri, $method);
        $response = $this->getContainer()->getRequestRunner()->run($request);
        return json_decode((string)$response->getBody());
    }
}
