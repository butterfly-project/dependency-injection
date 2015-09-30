<?php

namespace Butterfly\Component\DI\Tests\Compiler\ServiceCollector;

use Butterfly\Component\DI\Compiler\PreProcessing\ServiceFilter;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ServiceFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceFilter
     */
    protected $collector;

    protected function setUp()
    {
        $this->collector = new ServiceFilter();
    }

    public function testVisit()
    {
        $configuration = array(
            'service1' => array(
                'class'     => 'A',
                'arguments' => array(1, 2),
                'calls'     => array(
                    array('setA', array(1)),
                ),
                'properties'     => array(
                    array('p1', array(1)),
                ),
                'preTriggers' => array(
                    array('service' => '@trigger1', 'method' => 'beforeCreate1', 'arguments' => array('value1'))
                ),
                'postTriggers' => array(
                    array('service' => '@trigger1', 'method' => 'afterCreate1', 'arguments' => array('value1'))
                ),
                'alias'     => 'alias1',
                'tags'      => 'tag1',
            ),
            'service2' => array(
                'parent' => 'service1',
                'class'  => 'B',
                'calls'     => array(
                    array('setB', array(2)),
                ),
                'properties'     => array(
                    array('p2', array(2)),
                ),
                'preTriggers' => array(
                    array('service' => '@trigger2', 'method' => 'beforeCreate2', 'arguments' => array('value2'))
                ),
                'postTriggers' => array(
                    array('service' => '@trigger2', 'method' => 'afterCreate2', 'arguments' => array('value2'))
                ),
            ),
            'service3' => array('class' => 'C')
        );

        $expectedConfiguration = array(
            'service1' => array(
                'class'     => 'A',
                'arguments' => array(1, 2),
                'calls'     => array(
                    array('setA', array(1)),
                ),
                'properties'     => array(
                    array('p1', array(1)),
                ),
                'preTriggers' => array(
                    array('service' => '@trigger1', 'method' => 'beforeCreate1', 'arguments' => array('value1')),
                ),
                'postTriggers' => array(
                    array('service' => '@trigger1', 'method' => 'afterCreate1', 'arguments' => array('value1')),
                ),
                'alias'     => 'alias1',
                'tags'      => 'tag1',
            ),
            'service3' => array(
                'class' => 'C',
            ),
            'service2' => array(
                'class'     => 'B',
                'arguments' => array(1, 2),
                'calls'     => array(
                    array('setA', array(1)),
                    array('setB', array(2)),
                ),
                'properties'     => array(
                    array('p1', array(1)),
                    array('p2', array(2)),
                ),
                'preTriggers' => array(
                    array('service' => '@trigger1', 'method' => 'beforeCreate1', 'arguments' => array('value1')),
                    array('service' => '@trigger2', 'method' => 'beforeCreate2', 'arguments' => array('value2')),
                ),
                'postTriggers' => array(
                    array('service' => '@trigger1', 'method' => 'afterCreate1', 'arguments' => array('value1')),
                    array('service' => '@trigger2', 'method' => 'afterCreate2', 'arguments' => array('value2')),
                ),
            ),
        );

        $this->assertEquals($expectedConfiguration, $this->collector->filter($configuration));
    }

    /**
     * @expectedException \Butterfly\Component\DI\Compiler\ServiceVisitor\InvalidConfigurationException
     */
    public function testVisitIfUndefinedParentService()
    {
        $this->collector->filter(array(
            'service2' => array(
                'parent' => 'service1',
                'class'  => 'B',
            ),
        ));
    }
}
