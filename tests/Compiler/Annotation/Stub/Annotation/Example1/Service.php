<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example1;

use Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation\Example1\DirA\InnerService;

/**
 * @service   service.base
 * @arguments ["service.inner"]
 * @calls [
 *     ["setParameterA", ["%parameter.a%"]],
 *     ["setParameterB", ["%parameter.b%"]]
 * ]
 * @properties {
 *     "propertyA": "%parameter_of_property.a%",
 *     "propertyB": "%parameter_of_property.b%"
 * }
 * @scope factory
 * @tags  service.tag
 * @preTriggers [
 *     {
 *         "service":   "service.trigger",
 *         "method":    "beforeCreate",
 *         "arguments": ["%parameter.a%"]
 *     },
 *     {
 *         "class":     "Me\\Trigger",
 *         "method":    "beforeCreate",
 *         "arguments": ["%parameter.a%"]
 *     }
 * ]
 * @postTriggers [
 *     {
 *         "service":   "service.trigger",
 *         "method":    "afterCreate",
 *         "arguments": ["%parameter.b%"]
 *     },
 *     {
 *         "class":     "Me\\Trigger",
 *         "method":    "afterCreate",
 *         "arguments": ["%parameter.b%"]
 *     }
 * ]
 */
class Service
{
    /**
     * @var InnerService
     */
    protected $innerService;

    /**
     * @var string
     */
    protected $parameterA;

    /**
     * @var string
     */
    protected $parameterB;

    /**
     * @var int
     */
    protected $propertyA;

    /**
     * @var int
     */
    protected $propertyB;

    /**
     * @param InnerService $innerService
     */
    public function __construct(InnerService $innerService)
    {
        $this->innerService = $innerService;
    }

    /**
     * @param string $parameterA
     */
    public function setParameterA($parameterA)
    {
        $this->parameterA = $parameterA;
    }

    /**
     * @param string $parameterB
     */
    public function setParameterB($parameterB)
    {
        $this->parameterB = $parameterB;
    }
}
