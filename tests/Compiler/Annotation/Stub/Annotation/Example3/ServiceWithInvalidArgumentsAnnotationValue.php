<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example3;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example3\DirA\InnerService;

/**
 * @service service.incrorrect
 * @arguments incorrect.argument
 */
class ServiceWithInvalidArgumentsAnnotationValue
{
    /**
     * @var InnerService
     */
    protected $innerService;

    /**
     * @param InnerService $innerService
     */
    public function __construct(InnerService $innerService)
    {
        $this->innerService = $innerService;
    }
}
