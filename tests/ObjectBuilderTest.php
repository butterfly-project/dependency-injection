<?php

namespace Butterfly\Component\DI\Tests;

use Butterfly\Component\DI\ObjectBuilder;
use Butterfly\Component\DI\Tests\Stubs\FactoryOutputService;
use Butterfly\Component\DI\Tests\Stubs\FactoryService;
use Butterfly\Component\DI\Tests\Stubs\ServiceStub;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ObjectBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectBuilder
     */
    protected $objectBuilder;

    protected function setUp()
    {
        $this->objectBuilder = new ObjectBuilder();
    }

    public function testNativeCreate()
    {
        /** @var FactoryOutputService $service */
        $service = $this
            ->objectBuilder
            ->nativeCreate('\Butterfly\Component\DI\Tests\Stubs\FactoryOutputService', array(1, 2))
            ->getObject();

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildObjectException
     */
    public function testNativeCreateIfUndefinedClass()
    {
        $this
            ->objectBuilder
            ->nativeCreate('\Butterfly\Component\DI\Tests\UndefinedClass', array(1, 2));
    }

    public function testStaticFactoryMethodCreate()
    {
        /** @var FactoryOutputService $service */
        $service = $this
            ->objectBuilder
            ->staticFactoryMethodCreate('\Butterfly\Component\DI\Tests\Stubs\FactoryService', 'createInstance', array(1, 2))
            ->getObject();

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildObjectException
     */
    public function testStaticFactoryMethodCreateIfUndefinedClass()
    {
        $this
            ->objectBuilder
            ->staticFactoryMethodCreate('\Butterfly\Component\DI\Tests\UndefinedClass', 'createInstance', array(1, 2));
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildObjectException
     */
    public function testStaticFactoryMethodCreateIfUndefinedMethod()
    {
        $this
            ->objectBuilder
            ->staticFactoryMethodCreate('\Butterfly\Component\DI\Tests\Stubs\FactoryService', 'undefinedMethod', array(1, 2));
    }

    public function testFactoryMethodCreate()
    {
        /** @var FactoryOutputService $service */
        $service = $this
            ->objectBuilder
            ->factoryMethodCreate(new FactoryService(), 'create', array(1, 2))
            ->getObject();

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildObjectException
     */
    public function testFactoryMethodCreateIfUndefinedClass()
    {
        $this
            ->objectBuilder
            ->factoryMethodCreate(new FactoryService(), 'undefinedMethod', array(1, 2));
    }

    public function testCallObjectMethod()
    {
        /** @var ServiceStub $service */
        $service = $this
            ->objectBuilder
            ->nativeCreate('\Butterfly\Component\DI\Tests\Stubs\ServiceStub')
            ->callObjectMethod('setB', array(1))
            ->callObjectMethod('setC', array(2))
            ->getObject();

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service);
        $this->assertEquals(1, $service->getB());
        $this->assertEquals(2, $service->getC());
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildObjectException
     */
    public function testCallObjectMethodIfUndefinedMethod()
    {
        $this
            ->objectBuilder
            ->nativeCreate('\Butterfly\Component\DI\Tests\Stubs\ServiceStub')
            ->callObjectMethod('setG', array(1));
    }

    public function testCallObjectMethodIfForced()
    {
        $this
            ->objectBuilder
            ->nativeCreate('\Butterfly\Component\DI\Tests\Stubs\ServiceStubWithMagicSetter')
            ->callObjectMethod('setA', array(1), true);
    }

    public function testSetObjectProperty()
    {
        /** @var ServiceStub $service */
        $service = $this
            ->objectBuilder
            ->nativeCreate('\Butterfly\Component\DI\Tests\Stubs\ServiceStub')
            ->setObjectProperty('a', 123)
            ->getObject();

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service);
        $this->assertEquals(123, $service->a);
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildObjectException
     */
    public function testSetObjectPropertyIfUndefinedProperty()
    {
        $this
            ->objectBuilder
            ->nativeCreate('\Butterfly\Component\DI\Tests\Stubs\ServiceStub')
            ->setObjectProperty('undefined_property', array(1));
    }
}
