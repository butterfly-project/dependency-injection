<?php

namespace Butterfly\Component\DI\Tests\Compiler\ServiceCollector;

use Butterfly\Component\DI\Compiler\PreProcessing\TagFilter;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class TagFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testVisit()
    {
        $filter = new TagFilter();

        $configuration = array(
            'service1' => array('class' => 'A', 'tags' => 'tag1'),
            'service2' => array('class' => 'B', 'tags' => array('tag1', 'tag2')),
            'service3' => array('class' => 'C'),
        );

        $expectedConfiguration = array(
            'service1' => array('class' => 'A', 'tags' => 'tag1'),
            'service2' => array('class' => 'B', 'tags' => array('tag1', 'tag2')),
            'service3' => array('class' => 'C'),
            'tags' => array(
                'tag1' => array('service1', 'service2'),
                'tag2' => array('service2'),
            )
        );

        $this->assertEquals($expectedConfiguration, $filter->filter($configuration));
    }
}
