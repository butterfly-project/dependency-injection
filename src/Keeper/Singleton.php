<?php

namespace Butterfly\Component\DI\Keeper;

class Singleton extends AbstractKeeper
{
    /**
     * @var array
     */
    protected $services;

    /**
     * @param string $id
     * @param array $configuration
     * @return Object
     */
    public function buildObject($id, array $configuration)
    {
        if (empty($this->services[$id])) {
            $this->services[$id] = $this->build($configuration);
        }

        return $this->services[$id];
    }
}
