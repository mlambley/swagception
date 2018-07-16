<?php
namespace Swagception;

trait Schema
{
    /**
     * @var SwaggerSchema
     */
    protected $swaggerSchema;

    /**
     * @return SwaggerSchema
     */
    public function _getSwaggerSchema()
    {
        return $this->swaggerSchema;
    }
}
