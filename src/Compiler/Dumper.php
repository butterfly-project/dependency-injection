<?php

namespace Butterfly\Component\DI\Compiler;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class Dumper
{
    /**
     * @param string $filepath
     * @return array|null
     */
    public static function getConfig($filepath)
    {
        return is_readable($filepath) ? require $filepath : null;
    }

    /**
     * @param string $filepath
     * @param array $configuration
     * @throws \InvalidArgumentException if output configuration file is not writable
     */
    public static function dump(array $configuration, $filepath)
    {
        if (!self::isWritable($filepath)) {
            throw new \InvalidArgumentException(sprintf("Output configuration file '%s' is not writable", $filepath));
        }

        $data = sprintf("<?php return %s;", var_export($configuration, true));

        file_put_contents($filepath, $data);
    }

    /**
     * @param string $filepath
     */
    public static function remove($filepath)
    {
        if (self::isWritable($filepath)) {
            unlink($filepath);
        }
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
