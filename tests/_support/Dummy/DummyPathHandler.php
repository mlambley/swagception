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
            '/users/{userID}'       => [
                200 => '/users/1',
                201 => '/users/1',
                204 => '/users/1',
                404 => '/users/4',
            ],
            '/comments/{commentID}' => [
                200 => '/comments/1',
                201 => '/comments/1',
                204 => '/comments/1',
                404 => '/comments/4',
            ],
        ];
    }

    public function convertPath($path, $method, $statusCode)
    {
        if (isset($this->replacements[$path][$statusCode])) {
            return $this->replacements[$path][$statusCode];
        }

        return $path;
    }
}
