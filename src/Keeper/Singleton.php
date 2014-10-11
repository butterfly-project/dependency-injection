<?php

namespace Butterfly\Component\DI\Keeper;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
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
