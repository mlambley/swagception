<?php
namespace Swagception\PathHandlerLoader;

interface ContainsHandlers
{
    /**
     * Returns an instance of the given handler class name. Use an existing instance, if present, unless we're forcing a new one to be created.
     *
     * @param string $handlerClassName
     * @param bool $forceCreate
     */
    public function get($handlerClassName, $forceCreate = false);
    /**
     * Unload the handler with the given class name, or if null, unload every handler in the container, including duplicates created using $forceCreate in the get function.
     *
     * @param string $handlerClassName
     */
    public function unload($handlerClassName = null);
}
