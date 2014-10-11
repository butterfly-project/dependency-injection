<?php

namespace Butterfly\Component\DI\Builder;

use Butterfly\Component\DI\Container;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class DiConfig
{
    /**
     * @var Container
     */
    private static $container;

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
        if (null === self::$container) {
            $containerConfig = require __DIR__ . '/config.php';

            self::$container = new Container($containerConfig);
        }

        return self::$container;
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
