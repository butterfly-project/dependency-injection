<?php

namespace Butterfly\Component\DI\Tests\Stubs;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
interface IServiceFooAware
{
    public function injectServiceFoo(ServiceFoo $service);
}
