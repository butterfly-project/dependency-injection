<?php

namespace Butterfly\Component\DI\Tests\Builder\ServiceVisitor\ServiceCollector;

use Butterfly\Component\DI\Builder\ServiceCollector\ServiceCollector;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ServiceCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceCollector
     */
    protected $collector;

    protected function setUp()
    {
        $this->collector = new ServiceCollector();
    }

    public function testVisit()
    {
        $this->collector->visit('service1', array(
            'class'     => 'A',
            'arguments' => array(1, 2),
            'alias'     => 'alias1',
            'tags'      => 'tag1',
        ));
        $this->collector->visit('service2', array(
            'parent' => 'service1',
            'class'  => 'B',
        ));
        $this->collector->visit('service3', array('class' => 'C'));

        $expectedConfiguration = array(
            'service1' => array(
                'class'     => 'A',
                'arguments' => array(1, 2),
                'alias'     => 'alias1',
                'tags'      => 'tag1',
            ),
            'service2' => array(
                'class'     => 'B',
                'arguments' => array(1, 2),
            ),
            'service3' => array(
                'class' => 'C',
            ),
        );

        $this->assertEquals($expectedConfiguration, $this->collector->getConfiguration());
    }

    /**
     * @expectedException \Butterfly\Component\DI\Builder\ServiceVisitor\InvalidConfigurationException
     */
    public function testVisitIfUndefinedParentService()
    {
        $this->collector->visit('service2', array(
            'parent' => 'service1',
            'class'  => 'B',
        ));

        $this->collector->getConfiguration();
    }
}
