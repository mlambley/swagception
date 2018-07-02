<?php
namespace tests\Dummy;

/**
 * @path /single/{id}
 */
class SinglePathHandler implements \Swagception\PathHandler\HandlesPath
{
    protected $replacements;
    
    public function __construct()
    {
        $this->replacements = [
            '/single/{id}' => '/single/1'
        ];
    }
    
    public function convertPath($path, $method, $statusCode)
    {
        if (isset($this->replacements[$path])) {
            return $this->replacements[$path];
        }
        
        return $path;
    }
}
