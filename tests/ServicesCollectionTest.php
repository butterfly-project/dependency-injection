<?php

namespace Butterfly\Component\DI\Tests;
use Butterfly\Component\DI\Container;
use Butterfly\Component\DI\ServicesCollection;
use Butterfly\Component\DI\Tests\Stubs\ServiceStub;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ServicesCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetServicesIds()
    {
        $serviceIds = array(
            'service.foo',
            'service.bar',
            'service.baz',
        );

        $container  = $this->getMockContainer();
        $collection = new ServicesCollection($container, $serviceIds);

        $this->assertEquals($serviceIds, $collection->getServicesIds());
    }

    public function testCount()
    {
        $serviceIds = array(
            'service.foo',
            'service.bar',
            'service.baz',
        );

        $container  = $this->getMockContainer();
        $collection = new ServicesCollection($container, $serviceIds);

        $this->assertCount(3, $collection);
    }

    public function testHas()
    {
        $serviceIds = array(
            'service.foo',
        );

        $container  = $this->getMockContainer();
        $container
            ->expects($this->any())
            ->method('has')
            ->with('service.foo')
            ->willReturn(true);

        $collection = new ServicesCollection($container, $serviceIds);

        $this->assertTrue($collection->has('service.foo'));
        $this->assertTrue(isset($collection['service.foo']));
    }

    public function testHasIfServiceIsNotAvailable()
    {
        $serviceIds = array();

        $container  = $this->getMockContainer();
        $container
            ->expects($this->never())
            ->method('hasService')
            ->with('service.foo')
            ->willReturn(true);

        $collection = new ServicesCollection($container, $serviceIds);

        $this->assertFalse($collection->has('service.foo'));
    }

    public function testGet()
    {
        $serviceIds = array(
            'service.foo',
        );

        $container  = $this->getMockContainer();
        $container
            ->expects($this->any())
            ->method('get')
            ->with('service.foo')
            ->willReturn(new ServiceStub());

        $collection = new ServicesCollection($container, $serviceIds);

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $collection->get('service.foo'));
        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $collection['service.foo']);
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\UndefinedInstanceException
     */
    public function testGetIfServiceIsNotAvailable()
    {
        $serviceIds = array();

        $container  = $this->getMockContainer();
        $container
            ->expects($this->never())
            ->method('getService')
            ->with('service.foo')
            ->willReturn(true);

        $collection = new ServicesCollection($container, $serviceIds);

        $collection->get('service.foo');
    }

    public function testSet()
    {
        $serviceIds = array(
            'service.foo',
        );

        $serviceObject = new ServiceStub();

        $container  = $this->getMockContainer();
        $container
            ->expects($this->any())
            ->method('setSyntheticService')
            ->with('service.foo', $serviceObject);

        $collection = new ServicesCollection($container, $serviceIds);

        $collection->set('service.foo', $serviceObject);
        $collection['service.foo'] = $serviceObject;
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\UndefinedInstanceException
     */
    public function testSetIfServiceIsNotAvailable()
    {
        $serviceIds    = array();
        $serviceObject = new ServiceStub();

        $container  = $this->getMockContainer();
        $container
            ->expects($this->never())
            ->method('setSyntheticService')
            ->with('service.foo', $serviceObject);

        $collection = new ServicesCollection($container, $serviceIds);

        $collection->set('service.foo', $serviceObject);
    }

    /**
     * @expectedException \LogicException
     */
    public function testUnset()
    {
        $serviceIds = array();

        $container = $this->getMockContainer();

        $collection = new ServicesCollection($container, $serviceIds);

        unset($collection['service.foo']);
    }

    public function testIteration()
    {
        $serviceIds    = array(
            'service.foo',
            'service.bar',
            'service.baz',
        );
        $serviceObject = new ServiceStub();

        $container  = $this->getMockContainer();
        $container
            ->expects($this->at(0))
            ->method('get')
            ->with('service.foo')
            ->willReturn($serviceObject);
        $container
            ->expects($this->at(1))
            ->method('get')
            ->with('service.bar')
            ->willReturn($serviceObject);
        $container
            ->expects($this->at(2))
            ->method('get')
            ->with('service.baz')
            ->willReturn($serviceObject);

        $collection = new ServicesCollection($container, $serviceIds);

        $i = 0;

        foreach ($collection as $service) {
            $i++;
            $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service);
        }

        $this->assertEquals(3, $i);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Container
     */
    protected function getMockContainer()
    {
        return $this
            ->getMockBuilder('\Butterfly\Component\DI\Container')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
