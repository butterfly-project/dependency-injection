<?php

namespace Syringe\Component\DI\Builder\Adapter;

use Syringe\Component\DI\Builder\Builder;
use Syringe\Component\DI\Builder\Parser\IParser;

abstract class AbstractConfigBuilder
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
     * @param array $configFiles
     * @return array
     * @throws \InvalidArgumentException if config file is not readable
     */
    protected function build(array $configFiles)
    {
        foreach ($configFiles as $configFile) {
            if (!is_readable($configFile)) {
                throw new \InvalidArgumentException(sprintf("Config file '%s' is not readable", $configFile));
            }

            $this->builder->addConfiguration($this->parser->parse($configFile));
        }

        return $this->builder->build();
    }

    /**
     * @param string $outputConfigFile
     * @param array $configuration
     */
    protected function exportConfig($outputConfigFile, array $configuration)
    {
        $data = sprintf("<?php return %s;", var_export($configuration, true));

        file_put_contents($outputConfigFile, $data);
    }
}
