<?php

namespace Butterfly\Component\DI\Builder\Parser;

class PhpParser implements IFileSupportedParser
{
    const SUPPORTED_FILE_EXTENSION = 'php';

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

        return require $file;
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
