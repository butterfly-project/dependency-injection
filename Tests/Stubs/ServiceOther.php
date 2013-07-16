<?php

namespace Syringe\Component\DI\Tests\Stubs;

class ServiceOther
{
    protected $internalService;

    public function injectServiceFoo(ServiceFoo $service)
    {
        $this->internalService = $service;
    }

    public function getInternalService()
    {
        return $this->internalService;
    }
}
