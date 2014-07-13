<?php

namespace Syringe\Component\DI\Tests\Integration\Builder;

use Syringe\Component\DI\Builder\SyringeBuilder;

class SyringeBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function getDataForTestBuild()
    {
        $dir = __DIR__ . '/configs/';

        return array(
            array(
                $dir . '/config_one.yml',
                $dir . '/config_one_result.php',
                SyringeBuilder::MODE_ONE_CONFIG,
                array(
                    'parameters' => array(),
                    'services'   => array(
                        'foo' => array(
                            'class' => '\Syringe\Component\DI\Tests\Stubs\ServiceStub',
                        ),
                    ),
                    'aliases'    => array(),
                    'tags'       => array(),
                ),
            ),

            array(
                $dir . '/config_multiple.php',
                $dir . '/config_multiple_result.php',
                SyringeBuilder::MODE_MULTIPLE_CONFIGS,
                array(
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
                    'aliases'    => array(),
                    'tags'       => array(),
                ),
            ),
        );
    }

    /**
     * @param string $input
     * @param string $output
     * @param string $mode
     * @param array $expected
     *
     * @dataProvider getDataForTestBuild
     */
    public function testBuild($input, $output, $mode, array $expected)
    {
        SyringeBuilder::build($input, $output, $mode);

        $this->assertEquals($expected, require $output);

        unlink($output);
    }
}
