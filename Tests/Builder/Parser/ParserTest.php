<?php

namespace Syringe\Component\DI\Tests\Builder\Parser;

use Syringe\Component\DI\Builder\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function getDataForTestParser()
    {
        $dir = __DIR__ . '/config';

        $expectedConfig = require $dir . '/expectedConfig.php';

        return array(
            array(new Parser\PhpParser(), $dir . '/config.php', $expectedConfig),
            array(new Parser\Sf2YamlParser(), $dir . '/config.yml', $expectedConfig),
            array(new Parser\JsonParser(), $dir . '/config.json', $expectedConfig),
        );
    }

    /**
     * @param Parser\IParser $parser
     * @param string $configFile
     * @param array $expectedConfig
     *
     * @dataProvider getDataForTestParser
     */
    public function testParser(Parser\IParser $parser, $configFile, array $expectedConfig)
    {
        $this->assertTrue($parser->isSupport($configFile));
        $this->assertEquals($expectedConfig, $parser->parse($configFile));
    }
}
