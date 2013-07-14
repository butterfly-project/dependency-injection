<?php

namespace Syringe\Tests;

use Syringe\Container;
use Syringe\Tests\Stubs\ComplexServiceStub;
use Syringe\Tests\Stubs\FactoryOutputService;
use Syringe\Tests\Stubs\ServiceInstanceCounter;
use Syringe\Tests\Stubs\ServiceStub;

/**
 * Class ContainerTest
 * @package Syringe\Tests
 *
 * Tests:
 * 1. Проверить наличие параметра
 * 2. Получить параметр
 *
 * 3. Проверить наличие сервиса
 * 4. Поднять сервис
 * 4а. Поднять через фабричный статический метод
 * 4б. Поднять через фабрику
 *
 * 5. Поднятие сервиса в режиме синглтона
 * 6. Поднятие сервиса в режиме фабрики (каждый запрос - новый инстанс)
 * 7. Поднятие сервиса в режиме прототипа (один инстанс - на каждый запрос клонирование)
 *
 * 8. Внедрение зависимости через конструктор
 * 9. Внедрение зависимости через метод
 * 10. Внедрение зависимости через публичное свойство
 *
 * 11. Внедрение зависимости по тегу
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $parameters = [
        'parameters' => [
            'parameter1' => 'a',
        ],
        'services'   => [
            'service.simple'                => [
                'class'     => 'Syringe\Tests\Stubs\ServiceStub',
                'arguments' => [1, 2]
            ],
            'undefined_class_service'       => [
                'class' => 'UndefinedClass',
            ],
            'service.static_factory_output' => [
                'factoryStaticMethod' => ['Syringe\Tests\Stubs\FactoryService', 'createInstance'],
                'arguments'           => [1, 2],
            ],
            'service.factory'               => [
                'class' => 'Syringe\Tests\Stubs\FactoryService',
            ],
            'service.factory_output'        => [
                'factoryMethod' => ['@service.factory', 'create'],
                'arguments'     => [1, 2],
            ],
            'service.scope.singleton'       => [
                'class' => 'Syringe\Tests\Stubs\ServiceInstanceCounter',
                'scope' => Container::SCOPE_SINGLETON
            ],
            'service.scope.factory'         => [
                'class' => 'Syringe\Tests\Stubs\ServiceInstanceCounter',
                'scope' => Container::SCOPE_FACTORY
            ],
            'service.scope.prototype'       => [
                'class' => 'Syringe\Tests\Stubs\ServiceInstanceCounter',
                'scope' => Container::SCOPE_PROTOTYPE
            ],
            'service.constructor_injection' => [
                'class'     => 'Syringe\Tests\Stubs\ComplexServiceStub',
                'arguments' => ['@service.simple']
            ],
            'service.setter_injection'      => [
                'class' => 'Syringe\Tests\Stubs\ComplexServiceStub',
                'calls' => [
                    ['setInternalService', ['@service.simple']],
                ]
            ],
            'service.property_injection'    => [
                'class'      => 'Syringe\Tests\Stubs\ComplexServiceStub',
                'properties' => [
                    'internalService' => '@service.simple',
                ]
            ],
            'service.tag_dependencies' => [
                'class' => 'Syringe\Tests\Stubs\FactoryOutputService',
                'arguments' => ['1', '#tag1'],
            ],
            'service.tag_dependencies.undefined_tag' => [
                'class' => 'Syringe\Tests\Stubs\FactoryOutputService',
                'arguments' => ['1', '#tag123'],
            ],
        ],
        'tags' => [
            'tag1' => ['@service.simple', '@service.factory_output']
        ],
    ];

    /**
     * @var Container
     */
    protected $container;

    protected function setUp()
    {
        $this->container = new Container($this->parameters);

        ServiceInstanceCounter::$countCreateInstances = 0;
        ServiceInstanceCounter::$countCloneInstances  = 0;
    }

    public function getDataForTestCreateContainerIfIncorrectConfiguration()
    {
        return [
            [[]],
            [['parameters', 'services']],
            [['parameters', 'tags']],
            [['services', 'tags']],
        ];
    }

    /**
     * @dataProvider getDataForTestCreateContainerIfIncorrectConfiguration
     * @param array $configuration
     * @expectedException \InvalidArgumentException
     */
    public function testCreateContainerIfIncorrectConfiguration(array $configuration)
    {
        new Container($configuration);
    }

    public function testHasParameter()
    {
        $this->assertTrue($this->container->hasParameter('parameter1'));
    }

    public function testHasParameterIfNoParameter()
    {
        $this->assertFalse($this->container->hasParameter('undefined_parameter'));
    }

    public function testGetParameter()
    {
        $this->assertEquals($this->parameters['parameters']['parameter1'], $this->container->getParameter('parameter1'));
    }

    /**
     * @expectedException \Syringe\Exception\UndefinedParameterException
     */
    public function testGetParameterIfNoParameter()
    {
        $this->container->getParameter('undefined_parameter');
    }

    public function testHasService()
    {
        $this->assertTrue($this->container->has('service.simple'));
    }

    public function testHasServiceIfNoService()
    {
        $this->assertFalse($this->container->has('undefined_service'));
    }

    public function testGetService()
    {
        /** @var ServiceStub $service */
        $service = $this->container->get('service.simple');

        $this->assertInstanceOf('\Syringe\Tests\Stubs\ServiceStub', $service);
        $this->assertEquals(1, $service->getB());
        $this->assertEquals(2, $service->getC());
    }

    /**
     * @expectedException \Syringe\Exception\UndefinedServiceException
     */
    public function testGetServiceIfNoService()
    {
        $this->container->get('undefined_service');
    }

    /**
     * @expectedException \Syringe\Exception\BuildServiceException
     */
    public function testGetServiceIfNoClassService()
    {
        $this->container->get('undefined_class_service');
    }

    public function testGetServiceThroughStaticFactoryMethod()
    {
        /** @var FactoryOutputService $service */
        $service = $this->container->get('service.static_factory_output');

        $this->assertInstanceOf('\Syringe\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    public function testGetServiceThroughFactory()
    {
        /** @var FactoryOutputService $service */
        $service = $this->container->get('service.factory_output');

        $this->assertInstanceOf('\Syringe\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    public function testGetServiceWithSingletonScope()
    {
        $this->assertEquals(0, ServiceInstanceCounter::$countCreateInstances);

        /** @var ServiceInstanceCounter $service */
        $service = $this->container->get('service.scope.singleton');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);

        /** @var ServiceInstanceCounter $service2 */
        $service2 = $this->container->get('service.scope.singleton');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);
    }

    public function testGetServiceWithFactoryScope()
    {
        $this->assertEquals(0, ServiceInstanceCounter::$countCreateInstances);

        /** @var ServiceInstanceCounter $service */
        $service = $this->container->get('service.scope.factory');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);

        /** @var ServiceInstanceCounter $service2 */
        $service2 = $this->container->get('service.scope.factory');

        $this->assertEquals(2, ServiceInstanceCounter::$countCreateInstances);
    }

    public function testGetServiceWithPrototypeScope()
    {
        $this->assertEquals(0, ServiceInstanceCounter::$countCreateInstances);
        $this->assertEquals(0, ServiceInstanceCounter::$countCloneInstances);

        /** @var ServiceInstanceCounter $service */
        $service = $this->container->get('service.scope.prototype');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);
        $this->assertEquals(0, ServiceInstanceCounter::$countCloneInstances);

        /** @var ServiceInstanceCounter $service2 */
        $service2 = $this->container->get('service.scope.prototype');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);
        $this->assertEquals(1, ServiceInstanceCounter::$countCloneInstances);
    }

    public function testConstructorInjection()
    {
        /** @var ComplexServiceStub $service */
        $service = $this->container->get('service.constructor_injection');

        $this->assertInstanceOf('\Syringe\Tests\Stubs\ComplexServiceStub', $service);
        $this->assertInstanceOf('\Syringe\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testSetterInjection()
    {
        /** @var ComplexServiceStub $service */
        $service = $this->container->get('service.setter_injection');

        $this->assertInstanceOf('\Syringe\Tests\Stubs\ComplexServiceStub', $service);
        $this->assertInstanceOf('\Syringe\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testPropertyInjection()
    {
        /** @var ComplexServiceStub $service */
        $service = $this->container->get('service.property_injection');

        $this->assertInstanceOf('\Syringe\Tests\Stubs\ComplexServiceStub', $service);
        $this->assertInstanceOf('\Syringe\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testHasTag()
    {
        $this->assertTrue($this->container->hasTag('tag1'));
    }

    public function testHasTagIfNoTag()
    {
        $this->assertFalse($this->container->hasTag('undefined_tag'));
    }

    public function testGetTagList()
    {
        $this->assertEquals(['tag1'], $this->container->getTagsList());
    }

    public function testGetServicesByTag()
    {
        $servicesByTag = $this->container->getServicesByTag('tag1');

        $this->assertCount(2, $servicesByTag);
    }

    /**
     * @expectedException \Syringe\Exception\UndefinedTagException
     */
    public function testGetServicesByTagIfNoTag()
    {
        $this->container->getServicesByTag('undefined_tag');
    }

    public function testTagDependency()
    {
        /** @var FactoryOutputService $service */
        $service = $this->container->get('service.tag_dependencies');

        $this->assertCount(2, $service->getB());
    }

    /**
     * @expectedException \Syringe\Exception\UndefinedTagException
     */
    public function testTagDependencyIfUndefinedTag()
    {
        $this->container->get('service.tag_dependencies.undefined_tag');
    }
}
