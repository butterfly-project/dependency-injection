<?php

namespace Butterfly\Component\DI\Tests\Stubs;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ServiceInstanceCounter
{
    public static $countCreateInstances = 0;
    public static $countCloneInstances  = 0;

    public function __construct()
    {
        self::$countCreateInstances++;
    }

    public function __clone()
    {
        self::$countCloneInstances++;
    }
}
