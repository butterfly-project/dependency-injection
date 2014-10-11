<?php

namespace Butterfly\Component\DI\Tests\Stubs;

interface IServiceFooAware 
{
    public function injectServiceFoo(ServiceFoo $service);
}
