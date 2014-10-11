<?php

namespace Butterfly\Component\DI\Builder\Parser;

class JsonParser implements IFileSupportedParser
{
    const SUPPORTED_FILE_EXTENSION = 'json';

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

        return json_decode(file_get_contents($file), true);
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
