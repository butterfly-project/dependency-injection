<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example3;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example3\DirA\InnerService;

/**
 * @service service.incrorrect
 * @calls [
 *      ["setInnerService", "incrorrect.arguments"]
 * ]
 */
class ServiceWithInvalidCallsAnnotation4
{
    /**
     * @var InnerService
     */
    protected $innerService;

    /**
     * @param InnerService $innerService
     */
    public function setInnerService($innerService)
    {
        $this->innerService = $innerService;
    }
}
