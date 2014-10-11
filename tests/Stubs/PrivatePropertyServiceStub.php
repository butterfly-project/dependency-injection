<?php

namespace Butterfly\Component\DI\Tests\Stubs;

class PrivatePropertyServiceStub
{
    private $internalService;

    public function getInternalService()
    {
        return $this->internalService;
    }
}
