<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example3\DirA;

class InnerService
{
    protected $a;
    protected $b;

    public function setA($a)
    {
        $this->a = $a;
    }

    public function setB($b)
    {
        $this->b = $b;
    }
}
