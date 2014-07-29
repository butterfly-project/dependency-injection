<?php

namespace Syringe\Component\DI\Tests\Builder;

use Syringe\Component\DI\Builder\SyringeBuilder;

class SyringeBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $dir    = __DIR__ . '/config/';
        $input  = $dir . '/config_one.yml';
        $output = $dir . '/config_one_result.php';

        SyringeBuilder::build($input, $output);

        $expected = array(
            'parameters' => array(),
            'services'   => array(
                'foo' => array(
                    'class' => '\Syringe\Component\DI\Tests\Stubs\ServiceStub',
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

        SyringeBuilder::buildForArray($inputConfigs, $output);

        $expected = array(
            'parameters' => array(),
            'services'   => array(
                'foo' => array(
                    'class' => '\Syringe\Component\DI\Tests\Stubs\ServiceStub',
                ),
                'bar' => array(
                    'class' => '\Syringe\Component\DI\Tests\Stubs\ServiceStub',
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
