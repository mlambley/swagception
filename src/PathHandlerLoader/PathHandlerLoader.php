<?php
namespace Swagception\PathHandlerLoader;

use Swagception\PathHandler\HandlesPath;

/**
 * Accepts a namespace and a path. Requires all files in that path.
 * Fetches classes which are in the namespace, implements the HandlesPath interface, and contain the specified annotation.
 */
class PathHandlerLoader extends AnnotationHelper implements LoadsPathHandlers
{
    /**
     * @var \Swagception\SwaggerSchema Reference back to the schema object. Needed for the default path handler.
     */
    protected $schema;
    /**
     * @var string[] We link paths to classes via annotations. These are the annotation keys we search for.
     */
    protected $annotationKeys;
    /**
     * @var HandlesPath[] The handler cache.
     */
    protected $handlers;
    /**
     * @var closure[] These are applied to each custom path handler as it's created. Use this to configure the handlers.
     */
    protected $onHandlerLoad;
    /**
     * @var closure[] These are applied to each custom path handler as it's destroyed. Clean up any temporary data you've created here.
     */
    protected $onHandlerUnload;
    /**
     * @var bool Whether we use the default path handler (which fetches enum and x-example for parameters) or whether we're always going to use our own path handler classes.
     */
    protected $useDefaultPathHandler;

    public function __construct($schema)
    {
        $this->schema = $schema;
        $this->loadAnnotationKeys();
        $this->handlers = [];
        $this->onHandlerLoad = [];
        $this->onHandlerUnload = [];
        $this->handledPaths = [];
        $this->useDefaultPathHandler = true;
    }

    /**
     * @param string[] $annotationKeys
     * @return static
     */
    public function withAnnotationKeys($annotationKeys)
    {
        $this->annotationKeys = $annotationKeys;
        return $this;
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
     * @param bool $useDefaultPathHandler
     * @return static
     */
    public function useDefaultPathHandler($useDefaultPathHandler)
    {
        $this->useDefaultPathHandler = $useDefaultPathHandler;
        return $this;
    }

    /**
     * @param string $path
     * @return HandlesPath
     */
    public function getHandler($path)
    {
        $handlerClassName = $this->getClass($path);
        if (empty($handlerClassName)) {
            if (!$this->useDefaultPathHandler) {
                throw new \Exception(sprintf('There is no path handler configured for %1$s', $path));
            }
            //Don't need to cache the default path handler.
            return $this->getDefaultPathHandler();
        } else {
            return $this->getHandlerFromClass($handlerClassName);
        }
    }

    /**
     * @param string $handlerClassName
     * @return HandlesPath
     */
    public function getHandlerFromClass($handlerClassName)
    {
        //Look up handler from cache, and create if it doesn't exist.
        if (!isset($this->handlers[$handlerClassName])) {
            $this->handlers[$handlerClassName] = $this->getCustomPathHandler($handlerClassName);
        }
        return $this->handlers[$handlerClassName];
    }

    /**
     * @return static
     */
    public function unloadHandlers()
    {
        foreach ($this->handlers as $Handler) {
            foreach ($this->onHandlerUnload as $closure) {
                $closure($Handler);
            }
        }
        $this->handlers = array();
        return $this;
    }

    public function getHandledPaths()
    {
        $this->loadData();
        return array_keys($this->cache);
    }

    protected function getDefaultPathHandler()
    {
        return (new \Swagception\PathHandler\DefaultPathHandler($this->schema));
    }

    protected function getCustomPathHandler($handlerClassName)
    {
        $PathHandler = new $handlerClassName();
        foreach ($this->onHandlerLoad as $closure) {
            $closure($PathHandler);
        }
        return $PathHandler;
    }

    protected function loadAnnotationKeys()
    {
        $this->annotationKeys = ['path'];
    }

    protected function getClass($path)
    {
        $this->loadData();
        return isset($this->cache[$path]) ? $this->cache[$path] : null;
    }

    protected function updateCacheWith($class, $annotations)
    {
        if (in_array('Swagception\\PathHandler\\HandlesPath', class_implements($class))) {
            foreach ($this->annotationKeys as $key) {
                if (isset($annotations[$key])) {
                    if (!is_array($annotations[$key])) {
                        $annotations[$key] = [$annotations[$key]];
                    }
                    foreach ($annotations[$key] as $path) {
                        $this->updatePathClass($class, $path);
                    }
                }
            }
        }
    }

    protected function updatePathClass($class, $path)
    {
        if (isset($this->cache[$path])) {
            throw new \Exception(sprintf('Classes %1$s and %2$s are both marked as handling path %3$s', $this->cache[$path], $class, $path));
        }
        $this->cache[$path] = $class;
    }
}
