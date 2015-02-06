<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Autowired\Example4;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Autowired\Example4\DirA\InnerService;
use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Autowired\Example4\DirA\Inner2Service;

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
