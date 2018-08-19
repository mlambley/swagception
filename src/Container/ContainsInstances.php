<?php
namespace Swagception\Container;

interface ContainsInstances
{
    /**
     * @return \Swagception\SwaggerSchema
     */
    public function getSchema();
    /**
     * @return \Swagception\Request\Runner\RunsRequests
     */
    public function getRequestRunner();
    /**
     * @return \Swagception\Request\Generator\GeneratesRequests
     */
    public function getRequestGenerator();
    /**
     * @return \Swagception\PathHandlerLoader\LoadsPathHandlers
     */
    public function getPathHandlerLoader();
    /**
     * @return \Swagception\Validator\CanValidate
     */
    public function getValidator();
    /**
     * @return \Swagception\PathHandlerLoader\ContainsHandlers
     */
    public function getHandlerContainer();
}
