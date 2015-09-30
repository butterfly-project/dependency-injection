<?php

namespace Butterfly\Component\DI\Compiler;

use Butterfly\Component\DI\Compiler\PreProcessing\IFilter;
use Butterfly\Component\DI\Compiler\PreProcessing\ParameterResolver\Resolver;
use Butterfly\Component\DI\Compiler\PreProcessing\ServiceFilter;
use Butterfly\Component\DI\Compiler\PreProcessing\TagFilter;
use Butterfly\Component\Form\ScalarConstraint;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ConfigCompiler
{
    /**
     * @var IFilter[]
     */
    protected $filters = array();

    /**
     * @param array $configuration
     * @return array
     */
    public static function compile(array $configuration)
    {
        return static::createInstance()->filter($configuration)->getValue();
    }

    /**
     * @return ScalarConstraint
     */
    public static function createInstance()
    {
        return ScalarConstraint::create()
            ->addTransformer(new Resolver())
            ->addTransformer(new ServiceFilter())
            ->addTransformer(new TagFilter());
    }
}
