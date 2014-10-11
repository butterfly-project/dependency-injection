<?php

namespace Butterfly\Component\DI\Tests\Builder;

use Butterfly\Component\DI\Builder\DiConfig;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class DiConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $input  = array(
            'services' => array(
                'foo' => array(
                    'class' => '\Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                )
            )
        );
        $output = __DIR__ . '/config_one_result.php';

        DiConfig::build($input, $output);

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
}
