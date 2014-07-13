<?php

namespace Syringe\Component\DI\Builder;

use Syringe\Component\DI\Container;

class SyringeBuilder
{
    const MODE_ONE_CONFIG       = 'one_config';
    const MODE_MULTIPLE_CONFIGS = 'multiple_configs';

    protected static $availableAdapters = array(
        self::MODE_ONE_CONFIG       => 'one_config.builder',
        self::MODE_MULTIPLE_CONFIGS => 'multiple_configs.builder',
    );

    /**
     * @param string $input
     * @param string $output
     * @param string $mode
     * @throws \InvalidArgumentException if output configuration file is not writable
     * @throws \InvalidArgumentException if mode is not available
     */
    public static function build($input, $output, $mode = self::MODE_ONE_CONFIG)
    {
        if (!self::isWritable($output)) {
            throw new \InvalidArgumentException(sprintf("Output configuration file '%s' is not writable", $output));
        }

        if (!isset(self::$availableAdapters[$mode])) {
            throw new \InvalidArgumentException(sprintf("Mode %s is not available"));
        }

        $containerConfig = require __DIR__ . '/config.php';
        $container       = new Container($containerConfig);

        $builder = $container->get(self::$availableAdapters[$mode]);
        $builder->run($input, $output);
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
