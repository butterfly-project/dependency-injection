<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example2;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example2\DirA\InnerService;

/**
 * @service service.factory
 */
class Service
{
    /**
     * @factory service.inner
     *
     * @return InnerService
     */
    public function createInnerService()
    {
        return new InnerService();

    }

    /**
     * @factory {
     *      "service":   "service.inner2",
     *      "arguments": ["%parameter.a%", "%parameter.b%"]
     * }
     *
     * @param string $a
     * @param string $b
     * @return InnerService
     */
    public function createInnerService2($a, $b)
    {
        $service = new InnerService();
        $service->setA($a);
        $service->setB($b);

        return $service;
    }

    /**
     * @factory service.inner3
     *
     * @return InnerService
     */
    public static function createInnerService3()
    {
        return new InnerService();
    }

    /**
     * @factory {
     *      "service":   "service.inner4",
     *      "arguments": ["%parameter.a%", "%parameter.b%"]
     * }
     *
     * @param string $a
     * @param string $b
     * @return InnerService
     */
    public static function createInnerService4($a, $b)
    {
        $service = new InnerService();
        $service->setA($a);
        $service->setB($b);

        return $service;
    }
}
