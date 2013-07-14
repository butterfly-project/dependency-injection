<?php

namespace Syringe\Tests\Stubs;

class FactoryService
{
    public static function createInstance($a, $b)
    {
        return new FactoryOutputService($a, $b);
    }

    public function create($a, $b)
    {
        return new FactoryOutputService($a, $b);
    }
}
