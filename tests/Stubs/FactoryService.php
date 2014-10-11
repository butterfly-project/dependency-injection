<?php

namespace Butterfly\Component\DI\Tests\Stubs;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
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
