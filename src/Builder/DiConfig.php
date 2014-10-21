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
     * @param string $outputPath
     * @param array $configuration
     * @throws \InvalidArgumentException if output configuration file is not writable
     */
    protected static function dumpConfig(array $configuration, $outputPath)
    {
        $tempPath = $outputPath . '.new';

        if (!self::isWritable($outputPath) || !self::isWritable($tempPath)) {
            throw new \InvalidArgumentException(sprintf("Output configuration file '%s' is not writable", $outputPath));
        }

        $data = sprintf("<?php return %s;", var_export($configuration, true));

        file_put_contents($tempPath, $data);

        if (is_readable($outputPath)) {
            unlink($outputPath);
        }

        rename($tempPath, $outputPath);
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
