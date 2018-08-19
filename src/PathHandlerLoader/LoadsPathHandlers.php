<?php
namespace Swagception\PathHandlerLoader;

use Swagception\PathHandler\HandlesPath;

interface LoadsPathHandlers
{
    /**
     * @param string $path
     * @return HandlesPath
     */
    public function getHandler($path);
}
