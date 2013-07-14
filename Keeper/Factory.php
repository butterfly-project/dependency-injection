<?php

namespace Syringe\Keeper;

class Factory extends AbstractKeeper
{
    /**
     * @param string $id
     * @param array $configuration
     * @return Object
     */
    public function buildObject($id, array $configuration)
    {
        return $this->build($configuration);
    }
}
