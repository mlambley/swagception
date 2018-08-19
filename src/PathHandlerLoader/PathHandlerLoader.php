<?php
namespace Swagception\PathHandlerLoader;

use Swagception\PathHandler\HandlesPath;
use Swagception\Exception;

/**
 * Accepts a namespace and a path. Requires all files in that path.
 * Fetches classes which are in the namespace, implements the HandlesPath interface, and contain the specified annotation.
 */
class PathHandlerLoader extends AnnotationHelper implements LoadsPathHandlers
{
    /**
     * @var \Swagception\Container\ContainsInstances Reference back to the schema object, and all other objects in the container.
     */
    protected $container;
    /**
     * @var string[] We link paths to classes via annotations. These are the annotation keys we search for.
     */
    protected $annotationKeys;
    /**
     * @var bool Whether we use the default path handler (which fetches enum and x-example for parameters) or whether we're always going to use our own path handler classes.
     */
    protected $useDefaultPathHandler;

    public function __construct($container)
    {
        $this->container = $container;
        $this->loadAnnotationKeys();
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
                throw new Exception\NoPathHandlerException(sprintf('There is no path handler configured for %1$s', $path));
            }
            //Don't need to cache the default path handler.
            return $this->getDefaultPathHandler();
        } else {
            return $this->container->getHandlerContainer()->get($handlerClassName);
        }
    }

    public function getHandledPaths()
    {
        $this->loadData();
        return array_keys($this->cache);
    }

    protected function getDefaultPathHandler()
    {
        return (new \Swagception\PathHandler\DefaultPathHandler($this->container));
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
            throw new Exception\ConfigurationException(sprintf('Classes %1$s and %2$s are both marked as handling path %3$s', $this->cache[$path], $class, $path));
        }
        $this->cache[$path] = $class;
    }
}
