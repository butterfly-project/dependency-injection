<?php

namespace Syringe\Component\DI\Builder\Parser;

class DelegatedParser implements IParser
{
    /**
     * @var IFileSupportedParser[]
     */
    protected $parsers = array();

    /**
     * @param IFileSupportedParser[] $parsers
     */
    public function __construct(array $parsers)
    {
        foreach ($parsers as $parser) {
            $this->addParser($parser);
        }
    }

    /**
     * @param IFileSupportedParser $parser
     */
    public function addParser(IFileSupportedParser $parser)
    {
        $this->parsers[] = $parser;
    }

    /**
     * @param string $file
     * @return array
     * @throws \InvalidArgumentException if file format is not supported
     */
    public function parse($file)
    {
        foreach ($this->parsers as $parser) {
            if ($parser->isSupport($file)) {
                return $parser->parse($file);
            }
        }

        throw new \InvalidArgumentException(sprintf("This file format '%s' is not supported", $file));
    }
}
