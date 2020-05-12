<?php

namespace Butterfly\Component\DI\Tests\Compiler\ServiceCollector;

use Butterfly\Component\DI\Compiler\ServiceCollector\AliasCollector;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class AliasCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AliasCollector
     */
    protected $collector;

    protected function setUp()
    {
        $this->collector = new AliasCollector();
    }

    public function testVisit()
    {
        $this->collector->visit('service1', array('alias' => 'service1.alias'));
        $this->collector->visit('service2', array('alias' => 'service2.alias'));
        $this->collector->visit('service3', array('class' => 'A'));

        $this->assertEquals(array(
            'service1' => 'service1.alias',
            'service2' => 'service2.alias',
        ), $this->collector->getConfiguration());
    }

    public function testVisitIfOtherConstructions()
    {
        $this->collector->visit('service1', array('class' => 'A', 'alias' => 'service1.alias'));
        $this->collector->visit('service2', array('alias' => 'service2.alias'));

        $this->assertEquals(array(
            'service2' => 'service2.alias',
        ), $this->collector->getConfiguration());
    }

    /**
     * @expectedException \Butterfly\Component\DI\Compiler\ServiceVisitor\InvalidConfigurationException
     */
    public function testVisitIfDuplicateAlias()
    {
        $this->collector->visit('service1', array('alias' => 'service.alias'));
        $this->collector->visit('service2', array('alias' => 'service.alias'));
    }
}
