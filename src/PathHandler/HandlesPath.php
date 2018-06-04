<?php
namespace Swagception\PathHandler;

interface HandlesPath
{
    /**
     * Convert a template path eg. /api/entity/{entityID}
     * To an actual path with a real entity id eg. /api/entity/5
     *
     * @param string $path
     * @param string $method
     * @return string
     */
    public function convertPath($path, $method = 'get');

    /**
     * Allows the path handler to refer back to the loaded schema.
     * Ensure that it returns $this
     *
     * @param object $schema
     * @return static
     */
    public function setSchema($schema);
}