<?php

namespace Butterfly\Component\DI\Tests\Compiler;

use Butterfly\Component\DI\Compiler\Dumper;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class DumperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfig()
    {
        $config   = array(1, 2, 3);
        $filepath = __DIR__ . '/config.php';

        $this->assertNull(Dumper::getConfig($filepath));

        Dumper::dump($config, $filepath);

        $this->assertEquals($config, Dumper::getConfig($filepath));

        Dumper::remove($filepath);
    }
}
