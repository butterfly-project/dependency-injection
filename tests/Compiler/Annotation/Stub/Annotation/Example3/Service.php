<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example3;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example3\DirA\InnerService;

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
