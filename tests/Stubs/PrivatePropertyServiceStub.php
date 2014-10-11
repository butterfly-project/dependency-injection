<?php

namespace Butterfly\Component\DI\Tests\Stubs;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class PrivatePropertyServiceStub
{
    private $internalService;

    public function getInternalService()
    {
        return $this->internalService;
    }
}
