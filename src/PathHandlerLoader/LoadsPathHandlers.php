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
    /**
     * Cleans up and destroys path handlers for the specified path.
     * Or all path handlers if path is null.
     *
     * @param string|null $path
     */
    public function unloadHandlers($path = null);
}
