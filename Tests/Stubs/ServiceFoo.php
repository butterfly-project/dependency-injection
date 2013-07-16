<?php

namespace Syringe\Component\DI\Tests\Stubs;

use Syringe\Component\DI\IInjector;

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
