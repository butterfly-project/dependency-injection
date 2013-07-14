<?php

namespace Syringe\Component\DI\Tests\Stubs;

class ComplexServiceStub
{
    public $internalService;

    public function __construct(ServiceStub $internalService = null)
    {
        $this->internalService = $internalService;
    }

    public function setInternalService(ServiceStub $internalService)
    {
        $this->internalService = $internalService;
    }

    public function getInternalService()
    {
        return $this->internalService;
    }
}
