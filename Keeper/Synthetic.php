<?php

namespace Syringe\Component\DI\Keeper;

use Syringe\Component\DI\Exception\BuildServiceException;

class Synthetic
{
    /**
     * @var array
     */
    protected $services = [];

    /**
     * @param string $id
     * @param array $configuration
     * @return Object
     */
    public function buildObject($id, array $configuration)
    {
        if (!array_key_exists($id, $this->services)) {
            throw new BuildServiceException(sprintf("Synthetic Service '%s' is not found", $id));
        }

        return $this->services[$id];
    }

    /**
     * @param string $id
     * @param Object $service
     */
    public function setService($id, $service)
    {
        $this->services[$id] = $service;
    }
}
