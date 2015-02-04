<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example4;

/**
 * @service service.base
 */
class Service
{
    /**
     * @autowired service.inner
     *
     * @var \stdClass
     */
    protected $innerService;

    /**
     * @autowired parameter.inner
     *
     * @var string
     */
    protected $innerProperty;
}
