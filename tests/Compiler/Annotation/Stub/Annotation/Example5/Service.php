<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example5;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example5\DirA\InnerService;
use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example5\DirA\Inner2Service;

/**
 * @service service.base
 */
class Service
{
    /**
     * @autowired
     */
    public function init(InnerService $innerService, Inner2Service $inner2Service)
    {

    }
}
