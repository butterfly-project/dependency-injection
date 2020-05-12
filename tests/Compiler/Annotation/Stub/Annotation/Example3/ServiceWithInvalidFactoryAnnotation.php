<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example3;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example3\DirA\InnerService;

/**
 * @service service.factory
 */
class ServiceWithInvalidFactoryAnnotation
{
    /**
     * @factory
     *
     * @return InnerService
     */
    public function createInnerService()
    {
        return new InnerService();

    }
}
