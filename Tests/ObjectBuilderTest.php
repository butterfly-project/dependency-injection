<?php

namespace Syringe\Tests;

use Syringe\ObjectBuilder;
use Syringe\Tests\Stubs\FactoryOutputService;
use Syringe\Tests\Stubs\FactoryService;
use Syringe\Tests\Stubs\ServiceStub;

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
            ->nativeCreate('\Syringe\Tests\Stubs\FactoryOutputService', [1, 2])
            ->getObject();

        $this->assertInstanceOf('\Syringe\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    /**
     * @expectedException \Syringe\Exception\BuildObjectException
     */
    public function testNativeCreateIfUndefinedClass()
    {
        $this
            ->objectBuilder
            ->nativeCreate('\Syringe\Tests\UndefinedClass', [1, 2]);
    }

    public function testStaticFactoryMethodCreate()
    {
        /** @var FactoryOutputService $service */
        $service = $this
            ->objectBuilder
            ->staticFactoryMethodCreate('\Syringe\Tests\Stubs\FactoryService', 'createInstance', [1, 2])
            ->getObject();

        $this->assertInstanceOf('\Syringe\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    /**
     * @expectedException \Syringe\Exception\BuildObjectException
     */
    public function testStaticFactoryMethodCreateIfUndefinedClass()
    {
        $this
            ->objectBuilder
            ->staticFactoryMethodCreate('\Syringe\Tests\UndefinedClass', 'createInstance', [1, 2]);
    }

    /**
     * @expectedException \Syringe\Exception\BuildObjectException
     */
    public function testStaticFactoryMethodCreateIfUndefinedMethod()
    {
        $this
            ->objectBuilder
            ->staticFactoryMethodCreate('\Syringe\Tests\Stubs\FactoryService', 'undefinedMethod', [1, 2]);
    }

    public function testFactoryMethodCreate()
    {
        /** @var FactoryOutputService $service */
        $service = $this
            ->objectBuilder
            ->factoryMethodCreate(new FactoryService(), 'create', [1, 2])
            ->getObject();

        $this->assertInstanceOf('\Syringe\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    /**
     * @expectedException \Syringe\Exception\BuildObjectException
     */
    public function testFactoryMethodCreateIfUndefinedClass()
    {
        $this
            ->objectBuilder
            ->factoryMethodCreate(new FactoryService(), 'undefinedMethod', [1, 2]);
    }

    public function testCallObjectMethod()
    {
        /** @var ServiceStub $service */
        $service = $this
            ->objectBuilder
            ->nativeCreate('\Syringe\Tests\Stubs\ServiceStub')
            ->callObjectMethod('setB', [1])
            ->callObjectMethod('setC', [2])
            ->getObject();

        $this->assertInstanceOf('\Syringe\Tests\Stubs\ServiceStub', $service);
        $this->assertEquals(1, $service->getB());
        $this->assertEquals(2, $service->getC());
    }

    /**
     * @expectedException \Syringe\Exception\BuildObjectException
     */
    public function testCallObjectMethodIfUndefinedMethod()
    {
        $this
            ->objectBuilder
            ->nativeCreate('\Syringe\Tests\ServiceStub')
            ->callObjectMethod('setG', [1]);
    }

    public function testSetObjectProperty()
    {
        /** @var ServiceStub $service */
        $service = $this
            ->objectBuilder
            ->nativeCreate('\Syringe\Tests\Stubs\ServiceStub')
            ->setObjectProperty('a', 123)
            ->getObject();

        $this->assertInstanceOf('\Syringe\Tests\Stubs\ServiceStub', $service);
        $this->assertEquals(123, $service->a);
    }

    /**
     * @expectedException \Syringe\Exception\BuildObjectException
     */
    public function testSetObjectPropertyIfUndefinedProperty()
    {
        $this
            ->objectBuilder
            ->nativeCreate('\Syringe\Tests\ServiceStub')
            ->setObjectProperty('undefined_property', [1]);
    }
}
