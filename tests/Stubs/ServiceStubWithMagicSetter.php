<?php

namespace Butterfly\Component\DI\Tests\Stubs;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 * @method setA
 */
class ServiceStubWithMagicSetter
{
    protected $a;

    public function __call($name, $arguments)
    {
        if ($name == 'setA' && count($arguments) == 1) {
            $this->a = reset($arguments);
        }
    }

    public function getA()
    {
        return $this->a;
    }
}
