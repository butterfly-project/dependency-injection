<?php

namespace Butterfly\Component\DI\Keeper;

use Butterfly\Component\DI\ServiceFactory;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
abstract class AbstractKeeper
{
    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(ServiceFactory $serviceFactory)
    {
        $this->serviceFactory = $serviceFactory;
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
        return $this->serviceFactory->create($configuration);
    }
}
