<?php

namespace Butterfly\Component\DI\Tests\Stubs;

class StaticTriggerService
{
    protected static $a;

    public static function setA($a)
    {
        self::$a = $a;
    }

    public static function getA()
    {
        return self::$a;
    }
}
