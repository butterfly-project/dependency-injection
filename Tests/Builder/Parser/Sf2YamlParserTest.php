<?php

namespace Syringe\Component\DI\Tests\Builder\Parser;

use Syringe\Component\DI\Builder\Parser\Sf2YamlParser;

class Sf2YamlParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseIfEmptyFile()
    {
        $configPath = __DIR__ . '/config/config_empty.yml';

        $parser = new Sf2YamlParser();

        $this->assertEquals(array(), $parser->parse($configPath));
    }
}
