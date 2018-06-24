<?php
namespace Swagception\PathHandlerLoader;

/**
 * Accepts a namespace and a path. Requires all files in that path.
 * Fetches classes which are in the namespace, implements the HandlesPath interface, and contain the specified annotation.
 */
class PathHandlerLoader extends AnnotationHelper implements LoadsPathHandlers
{
    protected $annotationKeys;
    
    public function __construct()
    {
        $this->loadAnnotationKeys();
    }

    public function withAnnotationKeys($annotationKeys)
    {
        $this->annotationKeys = $annotationKeys;
        return $this;
    }
    
    protected function loadAnnotationKeys()
    {
        $this->annotationKeys = ['path'];
    }

    public function getClass($path)
    {
        $this->loadData();
        return isset($this->cache[$path]) ? $this->cache[$path] : null;
    }
    
    protected function updateCacheWith($class, $annotations)
    {
        if (in_array('Swagception\\PathHandler\\HandlesPath', class_implements($class))) {
            foreach ($this->annotationKeys as $key) {
                if (isset($annotations[$key])) {
                    foreach ($annotations[$key] as $path) {
                        if (isset($this->cache[$path])) {
                            throw new \Exception(sprintf('Classes %1$s and %2$s are both marked as handling path %3$s', $this->cache[$path], $class, $path));
                        }
                        $this->cache[$path] = $class;
                    }
                }
            }
        }
    }
}
