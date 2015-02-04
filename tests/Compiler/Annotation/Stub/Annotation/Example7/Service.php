<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example7;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example7\DirA\InnerService;
use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example7\DirA\Inner2Service;

/**
 * @service service.base
 */
class Service
{
    /**
     * @autowired ["service.inner", "service.inner2", "%parameter.input%"]
     *
     * @param InnerService $innerService
     * @param Inner2Service $inner2Service
     * @param string $inputParameter
     */
    public function init(InnerService $innerService, Inner2Service $inner2Service, $inputParameter)
    {

    }
}
