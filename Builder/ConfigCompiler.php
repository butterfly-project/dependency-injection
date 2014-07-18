<?php

namespace Syringe\Component\DI\Builder;

use Syringe\Component\DI\Builder\Parser\IParser;

class ConfigCompiler
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var IParser
     */
    protected $parser;

    /**
     * @param Builder $builder
     * @param IParser $parser
     */
    public function __construct(Builder $builder, IParser $parser)
    {
        $this->builder = $builder;
        $this->parser  = $parser;
    }

    /**
     * @param array $configs
     * @return array
     */
    public function compile(array $configs)
    {
        foreach ($configs as $config) {
            $this->builder->addConfiguration($this->parser->parse($config));
        }

        return $this->builder->build();
    }
}
