<?php

namespace Butterfly\Component\DI\Tests\Stubs;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ServiceBar implements IServiceFooAware
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
