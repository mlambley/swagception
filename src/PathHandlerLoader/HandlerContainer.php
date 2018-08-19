<?php
namespace Swagception\PathHandlerLoader;

class HandlerContainer implements ContainsHandlers
{
    /**
     * @var \Swagception\Container\ContainsInstances Necessary for creating handler instances.
     */
    protected $container;
    /**
     * @var [string => \Swagception\PathHandler\HandlesPath]
     */
    protected $handlers;
    /**
     * @var closure[] These are applied to each handler as it's created. Use this to configure them.
     */
    protected $onHandlerLoad;
    /**
     * @var closure[] These are applied to each handler as it's destroyed. Clean up any temporary data you've created here.
     */
    protected $onHandlerUnload;

    public function __construct(\Swagception\Container\ContainsInstances $container)
    {
        $this->container = $container;
        $this->handlers = [];
        $this->onHandlerLoad = [];
        $this->onHandlerUnload = [];
    }

    /**
     * @param string $handlerClassName
     * @param bool $forceCreate
     * @return \Swagception\PathHandler\HandlesPath
     */
    public function get($handlerClassName, $forceCreate = false)
    {
        if ($forceCreate || !isset($this->handlers[$handlerClassName])) {
            return $this->load($handlerClassName);
        }
        return $this->handlers[$handlerClassName];
    }

    /**
     * @param closure $closure
     * @return static
     */
    public function onHandlerLoad($closure)
    {
        $this->onHandlerLoad[] = $closure;
        return $this;
    }

    /**
     * @param closure $closure
     * @return static
     */
    public function onHandlerUnload($closure)
    {
        $this->onHandlerUnload[] = $closure;
        return $this;
    }

    /**
     * @param string $handlerClassName
     * @return static
     */
    public function unload($handlerClassName = null)
    {
        if ($handlerClassName !== null) {
            if (isset($this->handlers[$handlerClassName])) {
                foreach ($this->onHandlerUnload as $closure) {
                    $closure($this->handlers[$handlerClassName]);
                }
                unset($this->handlers[$handlerClassName]);
            }
        } else {
            foreach (array_keys($this->handlers) as $handlerClassName) {
                $this->unload($handlerClassName);
            }
        }
        return $this;
    }

    /**
     * @param string $handlerClassName
     * @return \Swagception\PathHandler\HandlesPath
     * @throws \Exception
     */
    protected function load($handlerClassName)
    {
        $key = $handlerClassName;
        $cntr = 0;
        while (isset($this->handlers[$key])) {
            $key = $this->getRandomString($handlerClassName);
            $cntr++;
            if ($cntr > 1000) {
                //Not sure how this could happen, but better to check.
                throw new \Exception(sprintf('Could not generate a unique key for handler %1$s', $handlerClassName));
            }
        }
        $this->handlers[$key] = $this->loadInstance($handlerClassName);
        return $this->handlers[$key];
    }

    /**
     * @param string $handlerClassName
     * @return \Swagception\PathHandler\HandlesPath
     */
    protected function loadInstance($handlerClassName)
    {
        $handler = new $handlerClassName($this->container);
        foreach ($this->onHandlerLoad as $closure) {
            $closure($handler);
        }
        return $handler;
    }

    /**
     * @param string $beginningWith
     * @return string
     */
    protected function getRandomString($beginningWith)
    {
        //Doesn't need to be secure. Just needs to be unique.
        return $beginningWith . md5(microtime(true));
    }
}
