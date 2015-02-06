<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Autowired\Example2;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Autowired\Example2\DirA\InnerService;

/**
 * @service service.base
 */
class Service
{
    /**
     * @autowired
     *
     * @var InnerService
     */
    protected $inner;
}
