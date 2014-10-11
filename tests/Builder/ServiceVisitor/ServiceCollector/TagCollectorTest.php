<?php

namespace Butterfly\Component\DI\Tests\Builder\ServiceVisitor\ServiceCollector;

use Butterfly\Component\DI\Builder\ServiceCollector\TagCollector;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class TagCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TagCollector
     */
    protected $collector;

    protected function setUp()
    {
        $this->collector = new TagCollector();
    }

    public function testVisit()
    {
        $this->collector->visit('service1', array('class' => 'A', 'tags' => 'tag1'));
        $this->collector->visit('service2', array('class' => 'B', 'tags' => array('tag1', 'tag2')));
        $this->collector->visit('service3', array('class' => 'C'));

        $this->assertEquals(array(
            'tag1' => array('service1', 'service2'),
            'tag2' => array('service2'),
        ), $this->collector->getConfiguration());
    }
}
