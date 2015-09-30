<?php

namespace Butterfly\Component\DI\Compiler;

use Butterfly\Component\DI\Compiler\PreProcessing\IFilter;
use Butterfly\Component\DI\Compiler\PreProcessing\ParameterResolver\Resolver;
use Butterfly\Component\DI\Compiler\ServiceCollector\ServiceCollector;
use Butterfly\Component\DI\Compiler\ServiceCollector\TagCollector;

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
        return static::createInstance()->compileConfig($configuration);
    }

    /**
     * @return static
     */
    public static function createInstance()
    {
        return new static(array(
            new Resolver(),
            new ServiceCollector(),
            new TagCollector(),
        ));
    }

    /**
     * @param IFilter[] $filters
     */
    public function __construct(array $filters = array())
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * @param IFilter $filter
     * @return $this
     */
    public function addFilter(IFilter $filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @param array $configuration
     * @return array
     */
    public function compileConfig(array $configuration)
    {
        foreach ($this->filters as $filter) {
            $configuration = $filter->filter($configuration);
        }

        return $configuration;
    }
}
