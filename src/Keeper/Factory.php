<?php

namespace Butterfly\Component\DI\Keeper;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
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
