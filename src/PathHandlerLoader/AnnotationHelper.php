<?php
namespace Swagception\PathHandlerLoader;

use zpt\anno\Annotations;

abstract class AnnotationHelper
{
    protected $namespace;
    protected $filePath;
    protected $cache;

    /**
     * Check only classes whose namespace begins with the specified value.
     *
     * @param string $namespace
     * @return static
     */
    public function withNamespace($namespace)
    {
        if (strpos($namespace, '\\') === 0) {
            //get_declared_classes doesn't return the first backslash.
            $namespace = substr($namespace, 1);
        }
        
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Load all files in the specified file path.
     *
     * @param string $filePath
     * @return static
     */
    public function withFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * However you want to update the cache, given each class and its annotations.
     *
     * @param string $class
     * @param array $annotations
     */
    abstract protected function updateCacheWith($class, $annotations);

    protected function loadData()
    {
        if (!isset($this->cache)) {
            $classes = $this->getNamespaceClasses();

            //Now load the annotations for each class, and map the routes to the classes.
            $this->cache = array();
            foreach ($classes as $class) {
                $annotations = new Annotations(new \ReflectionClass($class));
                $this->updateCacheWith($class, $annotations);
            }
        }
    }

    protected function getNamespaceClasses()
    {
        $classes = array();
        if ($this->namespace !== null) {
            if ($this->filePath !== null) {
                $this->requireNamespaceClasses();
            }

            //Replace backslash with its ascii hex code.
            $regex = "/^" . str_replace('\\', '\x5C', $this->namespace) . ".*$/";
            
            //Loop through every declared class, looking for all classes in the namespace.
            foreach (get_declared_classes() as $name) {
                //Check whether the class is within the namespace.
                if (preg_match($regex, $name)) {
                    //Add the fully qualified class name to the class array.
                    $classes[] = $name;
                }
            }
        }

        return $classes;
    }

    protected function requireNamespaceClasses()
    {
        //Load all files in the class directory.
        $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->filePath));
        $phpFiles = new \RegexIterator($allFiles, '/\.php$/');
        foreach ($phpFiles as $phpFile) {
            require_once($phpFile->getRealPath());
        }
    }
}
