<?php

namespace Syringe\Component\DI\Tests\Stubs;

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
