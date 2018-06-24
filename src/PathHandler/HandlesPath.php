<?php
namespace Swagception\PathHandler;

interface HandlesPath
{
    /**
     * Convert a template path eg. /api/entity/{entityID}
     * To an actual path with a real entity id eg. /api/entity/5
     *
     * @param string $path
     * @return string
     */
    public function convertPath($path);
}
