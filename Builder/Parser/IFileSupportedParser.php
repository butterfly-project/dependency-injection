<?php

namespace Syringe\Component\DI\Builder\Parser;

interface IFileSupportedParser extends IParser
{
    /**
     * @param string $filePath
     * @return bool
     */
    public function isSupport($filePath);
}
