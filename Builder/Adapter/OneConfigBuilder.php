<?php

namespace Syringe\Component\DI\Builder\Adapter;

class OneConfigBuilder extends AbstractConfigBuilder
{
    /**
     * @param string $configFile
     * @param string $outputConfigFile
     * @throws \InvalidArgumentException if config file is not readable
     * @throws \InvalidArgumentException if IoCC configuration is invalid
     */
    public function run($configFile, $outputConfigFile)
    {
        $configuration = $this->build(array($configFile));

        $this->exportConfig($outputConfigFile, $configuration);
    }
}
