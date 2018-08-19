<?php
namespace Swagception;

trait ContainerTrait
{
    /**
     * @var Container\ContainsInstances
     */
    protected $swaggerContainer;

    /**
     * @return Container\ContainsInstances
     */
    public function _getSwaggerContainer()
    {
        return $this->swaggerContainer;
    }
}
