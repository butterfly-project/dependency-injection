<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Autowired\Example6;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Autowired\Example6\DirA\InnerService;
use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Autowired\Example6\DirA\Inner2Service;

/**
 * @service service.base
 */
class Service
{
    /**
     * @autowired
     *
     * @param InnerService $innerService
     * @param Inner2Service $inner2Service
     */
    public function init($innerService, $inner2Service)
    {

    }
}
