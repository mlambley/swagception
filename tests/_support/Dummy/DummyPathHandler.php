<?php
namespace tests\Dummy;

/**
 * @path /users/
 * @path /users/{userID}
 * @path /comments/
 * @path /comments/{commentID}
 */
class DummyPathHandler implements \Swagception\PathHandler\HandlesPath
{
    protected $replacements;
    
    public function __construct()
    {
        $this->replacements = [
            '/users/{userID}' => '/users/1',
            '/comments/{commentID}' => '/comments/1',
        ];
    }
    
    public function convertPath($path)
    {
        if (isset($this->replacements[$path])) {
            return $this->replacements[$path];
        }
        
        return $path;
    }
}
