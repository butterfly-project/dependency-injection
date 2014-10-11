<?php

namespace Butterfly\Component\DI\Tests\Stubs;

use Butterfly\Component\DI\IInjector;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
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
