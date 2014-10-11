<?php

namespace Butterfly\Component\DI\Tests\Stubs;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class UseStaticTriggerService
{
    protected $preA;

    public function __construct()
    {
        $this->preA = StaticTriggerService::getA();
    }

    public function getPreA()
    {
        return $this->preA;
    }
}
