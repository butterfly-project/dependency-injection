<?php

namespace Syringe\Component\DI\Builder;

use Syringe\Component\DI\Container;

class SyringeBuilder
{
    /**
     * @param string $configPath
     * @param string $output
     */
    public static function build($configPath, $output)
    {
        self::buildForArray(array($configPath), $output);
    }

    /**
     * @param array $configsPaths
     * @param string $output
     */
    public static function buildForArray(array $configsPaths, $output)
    {
        self::dumpConfig(self::getCompiler()->compile($configsPaths), $output);
    }

    /**
     * @return ConfigCompiler
     */
    protected static function getCompiler()
    {
        $containerConfig = require __DIR__ . '/config.php';
        $container       = new Container($containerConfig);

        return $container->get('config_compiler');
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
