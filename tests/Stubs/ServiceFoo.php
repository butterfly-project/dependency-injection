<?php

namespace Butterfly\Component\DI\Tests\Stubs;

use Butterfly\Component\DI\IInjector;

class ServiceFoo implements IInjector
{
    /**
     * @param Object $object
     * @return void
     */
    public function inject($object)
    {
        if ($object instanceof IServiceFooAware) {
            $object->injectServiceFoo($this);
        }
    }
}
