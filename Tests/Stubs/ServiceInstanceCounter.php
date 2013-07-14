<?php

namespace Syringe\Component\DI\Tests\Stubs;

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
