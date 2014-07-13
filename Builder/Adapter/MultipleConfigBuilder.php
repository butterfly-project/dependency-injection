<?php

namespace Syringe\Component\DI\Builder\Adapter;

use Syringe\Component\DI\Builder\Builder;
use Syringe\Component\DI\Builder\Parser\IParser;

class MultipleConfigBuilder extends AbstractConfigBuilder
{
    /**
     * @param string $configListFile
     * @param string $outputConfigFile
     * @throws \InvalidArgumentException if config list file is not readable
     * @throws \InvalidArgumentException if config list file is empty
     * @throws \InvalidArgumentException if config file is not readable
     * @throws \InvalidArgumentException if IoCC configuration is invalid
     */
    public function run($configListFile, $outputConfigFile)
    {
        if (!is_readable($configListFile)) {
            throw new \InvalidArgumentException(sprintf("Config list file '%s' is not readable", $configListFile));
        }

        $configList = require $configListFile;

        if (empty($configList)) {
            throw new \InvalidArgumentException("Config list file is empty");
        }

        $this->exportConfig($outputConfigFile, $this->build($configList));
    }
}
