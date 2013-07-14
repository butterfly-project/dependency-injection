<?php

namespace Syringe\Component\DI\Tests\Stubs;

class TriggerService 
{
    protected $a;

    public function __construct($a)
    {
        $this->a = $a;
    }

    public function setA($a)
    {
        $this->a = $a;
    }

    public function getA()
    {
        return $this->a;
    }
}
