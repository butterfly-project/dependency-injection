<?php

namespace Butterfly\Component\DI\Tests\Stubs;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
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
