<?php

namespace Syringe\Component\DI\Builder;

use Syringe\Component\DI\Builder\Parser\IParser;

class ContainerConfigurationBuilder
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
     * @param string $configListFile
     * @param string $outputConfigFile
     * @throws \InvalidArgumentException if config list file is not readable
     * @throws \InvalidArgumentException if output configuration file is not writable
     * @throws \InvalidArgumentException if config list file is empty
     * @throws \InvalidArgumentException if IoCC configuration is invalid
     */
    public function run($configListFile, $outputConfigFile)
    {
        if (!is_readable($configListFile)) {
            throw new \InvalidArgumentException(sprintf("Config list file '%s' is not readable", $configListFile));
        }

        if (!$this->isWritable($outputConfigFile)) {
            throw new \InvalidArgumentException(sprintf("Output configuration file '%s' is not writable", $outputConfigFile));
        }

        $configList = require $configListFile;

        if (empty($configList)) {
            throw new \InvalidArgumentException("Config list file is empty");
        }

        foreach ($configList as $configFile) {
            if (!is_readable($configFile)) {
                continue;
            }

            $this->builder->addConfiguration($this->parser->parse($configFile));
        }

        $configuration = $this->builder->build();

        file_put_contents($outputConfigFile, sprintf("<?php return %s;", var_export($configuration, true)));
    }

    /**
     * @param string $filePath
     * @return bool
     */
    protected function isWritable($filePath)
    {
        $checkingObject = file_exists($filePath) ? $filePath : dirname($filePath);

        return is_writable($checkingObject);
    }
}
 