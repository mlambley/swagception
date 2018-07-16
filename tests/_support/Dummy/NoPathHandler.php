<?php
/**
 *
 */
class NoPathHandler implements \Swagception\PathHandler\HandlesPath
{
    public function convertPath($path)
    {
        return $path;
    }
}
