<?php

namespace Butterfly\Component\DI\Tests\Builder;

use Butterfly\Component\DI\Builder\ButterflyBuilder;

class ButterflyBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $dir    = __DIR__ . '/config/';
        $input  = $dir . '/config_one.yml';
        $output = $dir . '/config_one_result.php';

        ButterflyBuilder::build($input, $output);

        $expected = array(
            'parameters' => array(),
            'services'   => array(
                'foo' => array(
                    'class' => '\Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                ),
            ),
            'interfaces' => array(),
            'aliases'    => array(),
            'tags'       => array(),
        );

        $this->assertEquals($expected, require $output);

        unlink($output);
    }

    public function testBuildForIterator()
    {
        $dir    = __DIR__ . '/config/';
        $inputConfigs  = array(
            $dir . '/config_multiple_1.yml',
            $dir . '/config_multiple_2.yml',
        );
        $output = $dir . '/config_one_result.php';

        ButterflyBuilder::buildForArray($inputConfigs, $output);

        $expected = array(
            'parameters' => array(),
            'services'   => array(
                'foo' => array(
                    'class' => '\Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                ),
                'bar' => array(
                    'class' => '\Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array('value1', 'value2'),
                ),
            ),
            'interfaces' => array(),
            'aliases'    => array(),
            'tags'       => array(),
        );

        $this->assertEquals($expected, require $output);

        unlink($output);
    }
}
