<?php

namespace Syringe\Component\DI\Tests\Stubs;

interface IServiceFooAware 
{
    public function injectServiceFoo(ServiceFoo $service);
}
