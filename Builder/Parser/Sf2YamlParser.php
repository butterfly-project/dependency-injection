<?php

namespace Syringe\Component\DI\Builder\Parser;

use Symfony\Component\Yaml\Yaml;

class Sf2YamlParser implements IFileSupportedParser
{
    const SUPPORTED_FILE_EXTENSION = 'yml';

    /**
     * @param string $file
     * @return array
     * @throws \InvalidArgumentException if file format is not supported
     */
    public function parse($file)
    {
        if (!$this->isSupport($file)) {
            throw new \InvalidArgumentException(sprintf("This file format '%s' is not supported", $file));
        }

        return Yaml::parse($file);
    }

    /**
     * @param string $filePath
     * @return bool
     */
    public function isSupport($filePath)
    {
        return (self::SUPPORTED_FILE_EXTENSION === pathinfo($filePath, PATHINFO_EXTENSION));
    }
}
