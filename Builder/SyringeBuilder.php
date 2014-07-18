<?php

namespace Syringe\Component\DI\Builder;

use Syringe\Component\DI\Builder\Parser\IParser;
use Syringe\Component\DI\Container;

class SyringeBuilder
{
    /**
     * @var Container
     */
    protected static $container;

    /**
     * @param string $configPath
     * @param string $output
     */
    public static function build($configPath, $output)
    {
        self::buildForArray(array($configPath), $output);
    }

    /**
     * @param array $configs
     * @param string $output
     */
    public static function buildForArray(array $configs, $output)
    {
        $builder = self::getBuilder();
        $parser  = self::getParser();

        foreach ($configs as $config) {
            $builder->addConfiguration($parser->parse($config));
        }

        self::dumpConfig($builder->build(), $output);
    }

    /**
     * @return Builder
     */
    protected static function getBuilder()
    {
        return self::getContainer()->get('builder');
    }

    /**
     * @return IParser
     */
    protected static function getParser()
    {
        return self::getContainer()->get('config_parser');
    }

    /**
     * @return Container
     */
    protected static function getContainer()
    {
        if (null !== self::$container) {
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
