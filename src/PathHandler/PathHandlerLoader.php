<?php
namespace Swagception\PathHandler;

use zpt\anno\Annotations;

class PathHandlerLoader
{
    /**
     * @var string Namespace for example data classes
     */
    protected $namespace;
    /**
     * @var string Folder path for requiring example data classes. If this is not specified, the classes must be already loaded.
     */
    protected $filePath;

    protected $defaultPathHandler;

    protected $cache;

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function withNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function withFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getClass($path)
    {
        if (!isset($this->cache)) {
            $this->getClasses();
        }

        return isset($this->cache[$path]) ? $this->cache[$path] : null;
    }

    public function getClasses()
    {
        if (!isset($this->cache)) {
            $classes = $this->getNamespaceClasses();
            //Now load the annotations for each class, and map the routes to the classes.

            $paths = array();
            foreach ($classes as $class) {
                if (in_array('Swagception\\PathHandler\\HandlesPath', class_implements($class))) {
                    $annotations = new Annotations(new \ReflectionClass($class));

                    if (isset($annotations['path'])) {
                        foreach ($annotations['path'] as $path) {
                            if (isset($paths[$path])) {
                                throw new \Exception(sprintf('Classes %1$s and %2$s are both marked as handling path %3$s', $paths[$path], $class, $path));
                            }
                            $paths[$path] = $class;
                        }
                    }
                }
            }
            $this->cache = $paths;
        }

        return $this->cache;
    }

    protected function getNamespaceClasses()
    {
        $classes = array();
        if (!empty($this->namespace)) {
            if (!empty($this->filePath)) {
                $this->requireNamespaceClasses();
            }

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
        //Load all files in the example class directory.
        $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->filePath));
        $phpFiles = new \RegexIterator($allFiles, '/\.php$/');
        foreach ($phpFiles as $phpFile) {
            require_once($phpFile->getRealPath());
        }
    }
}