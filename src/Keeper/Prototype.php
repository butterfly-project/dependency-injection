<?php

namespace Butterfly\Component\DI\Keeper;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class Prototype extends AbstractKeeper
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
        if (isset($this->services[$id])) {
            return clone $this->services[$id];
        }

        return $this->services[$id] = $this->build($configuration);
    }
}
