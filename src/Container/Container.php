<?php
namespace Swagception\Container;

class Container implements ContainsInstances
{
    /**
     * @var \Pimple\Container
     */
    protected $container;

    public function __construct()
    {
        $this->loadContainer();
        $this->loadSchema();
        $this->loadDefaultRequestRunner();
        $this->loadDefaultRequestGenerator();
        $this->loadDefaultPathHandlerLoader();
        $this->loadDefaultValidator();
        $this->loadDefaultHandlerContainer();
    }

    /**
     * @return \Pimple\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param \Pimple\Container $container
     * @return static
     */
    public function withContainer(\Pimple\Container $container)
    {
        $this->container = $container;
        return $this;
    }

    protected function loadContainer()
    {
        $this->container = new \Pimple\Container();
    }

    /**
     * @return \Swagception\SwaggerSchema
     */
    public function getSchema()
    {
        return $this->getContainer()['schema'];
    }

    /**
     * @param \Swagception\SwaggerSchema $schema
     * @return static
     */
    public function withSchema(\Swagception\SwaggerSchema $schema)
    {
        $this->getContainer()['schema'] = function ($c) use ($schema) {
            return $schema;
        };
        return $this;
    }

    protected function loadSchema()
    {
        $this->getcontainer()['schema'] = function ($c) {
            return \Swagception\SwaggerSchema::Create($this);
        };
    }

    /**
     * @return \Swagception\Request\Runner\RunsRequests
     */
    public function getRequestRunner()
    {
        return $this->getContainer()['requestRunner'];
    }

    /**
     * @param \Swagception\Request\Runner\RunsRequests $requestRunner
     * @return static
     */
    public function withRequestRunner(\Swagception\Request\Runner\RunsRequests $requestRunner)
    {
        $this->getContainer()['requestRunner'] = function ($c) use ($requestRunner) {
            return $requestRunner;
        };
        return $this;
    }

    protected function loadDefaultRequestRunner()
    {
        $this->getContainer()['requestRunner'] = function ($c) {
            return new \Swagception\Request\Runner\Runner();
        };
    }

    /**
     * @return \Swagception\Request\Generator\GeneratesRequests
     */
    public function getRequestGenerator()
    {
        return $this->getContainer()['requestGenerator'];
    }

    /**
     * @param \Swagception\Request\Generator\GeneratesRequests $requestGenerator
     * @return static
     */
    public function withRequestGenerator(\Swagception\Request\Generator\GeneratesRequests $requestGenerator)
    {
        $this->getContainer()['requestGenerator'] = function ($c) use ($requestGenerator) {
            return $requestGenerator;
        };
        return $this;
    }

    protected function loadDefaultRequestGenerator()
    {
        $this->getContainer()['requestGenerator'] = function ($c) {
            return new \Swagception\Request\Generator\Generator();
        };
    }

    /**
     * @return \Swagception\PathHandlerLoader\LoadsPathHandlers
     */
    public function getPathHandlerLoader()
    {
        return $this->getContainer()['pathHandlerLoader'];
    }

    /**
     * @param \Swagception\PathHandlerLoader\LoadsPathHandlers $pathHandlerLoader
     * @return static
     */
    public function withPathHandlerLoader(\Swagception\PathHandlerLoader\LoadsPathHandlers $pathHandlerLoader)
    {
        $this->getContainer()['pathHandlerLoader'] = function ($c) use ($pathHandlerLoader) {
            return $pathHandlerLoader;
        };
        return $this;
    }

    protected function loadDefaultPathHandlerLoader()
    {
        $this->getContainer()['pathHandlerLoader'] = function ($c) {
            return new \Swagception\PathHandlerLoader\PathHandlerLoader($this);
        };
    }

    /**
     * @return \Swagception\Validator\CanValidate
     */
    public function getValidator()
    {
        return $this->getContainer()['validator'];
    }

    /**
     * @param \Swagception\Validator\CanValidate $validator
     * @return static
     */
    public function withValidator(\Swagception\Validator\CanValidate $validator)
    {
        $this->getContainer()['validator'] = function ($c) use ($validator) {
            return $validator;
        };
        return $this;
    }

    protected function loadDefaultValidator()
    {
        $this->getContainer()['validator'] = function ($c) {
            return new \Swagception\Validator\Validator();
        };
    }

    /**
     * @return \Swagception\PathHandlerLoader\ContainsHandlers
     */
    public function getHandlerContainer()
    {
        return $this->getContainer()['handlerContainer'];
    }

    /**
     * @param \Swagception\PathHandlerLoader\ContainsHandlers $handlerContainer
     * @return static
     */
    public function withHandlerContainer(\Swagception\PathHandlerLoader\ContainsHandlers $handlerContainer)
    {
        $this->getContainer()['handlerContainer'] = function ($c) use ($handlerContainer) {
            return $handlerContainer;
        };
        return $this;
    }

    protected function loadDefaultHandlerContainer()
    {
        $this->getContainer()['handlerContainer'] = function ($c) {
            return new \Swagception\PathHandlerLoader\HandlerContainer($this);
        };
    }
}
