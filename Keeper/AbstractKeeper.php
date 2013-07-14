<?php

namespace Syringe\Keeper;

use Syringe\ServiceBuilder;

abstract class AbstractKeeper
{
    /**
     * @var ServiceBuilder
     */
    private $serviceBuilder;

    /**
     * @param ServiceBuilder $serviceBuilder
     */
    public function __construct(ServiceBuilder $serviceBuilder)
    {
        $this->serviceBuilder = $serviceBuilder;
    }

    /**
     * @param string $id
     * @param array $configuration
     * @return Object
     */
    abstract public function buildObject($id, array $configuration);

    /**
     * @param array $configuration
     * @return Object
     */
    protected function build(array $configuration)
    {
        return $this->serviceBuilder->build($configuration);
    }
}
