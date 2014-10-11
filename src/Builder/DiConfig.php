<?php

namespace Butterfly\Component\DI\Builder;

use Butterfly\Component\DI\Container;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class DiConfig
{
    /**
     * @param array $config
     * @param string $outputPath
     */
    public static function build(array $config, $outputPath)
    {
        $builder = self::getBuilder();

        $builder->setConfiguration($config);

        self::dumpConfig($builder->build(), $outputPath);
    }

    /**
     * @return ContainerConfigBuilder
     */
    protected static function getBuilder()
    {
        return self::getContainer()->get('builder');
    }

    /**
     * @return Container
     */
    protected static function getContainer()
    {
        $containerConfig = require __DIR__ . '/config.php';

        return new Container($containerConfig);
    }

    /**
     * @param string $outputConfigFile
     * @param array $configuration
     * @throws \InvalidArgumentException if output configuration file is not writable
     */
    protected static function dumpConfig(array $configuration, $outputConfigFile)
    {
        if (!self::isWritable($outputConfigFile)) {
            throw new \InvalidArgumentException(sprintf("Output configuration file '%s' is not writable", $outputConfigFile));
        }

        $data = sprintf("<?php return %s;", var_export($configuration, true));

        file_put_contents($outputConfigFile, $data);
    }

    /**
     * @param string $filePath
     * @return bool
     */
    protected static function isWritable($filePath)
    {
        $checkingObject = file_exists($filePath) ? $filePath : dirname($filePath);

        return is_writable($checkingObject);
    }
}
