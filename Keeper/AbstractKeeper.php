<?php

namespace Syringe\Component\DI\Keeper;

use Syringe\Component\DI\ServiceFactory;

abstract class AbstractKeeper
{
    /**
     * @var ServiceFactory
     */
    private $serviceBuilder;

    /**
     * @param ServiceFactory $serviceBuilder
     */
    public function __construct(ServiceFactory $serviceBuilder)
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
