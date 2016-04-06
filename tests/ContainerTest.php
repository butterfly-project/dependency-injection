<?php

namespace Butterfly\Component\DI\Tests;

use Butterfly\Component\DI\Container;
use Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub;
use Butterfly\Component\DI\Tests\Stubs\FactoryOutputService;
use Butterfly\Component\DI\Tests\Stubs\PrivatePropertyServiceStub;
use Butterfly\Component\DI\Tests\Stubs\ServiceInstanceCounter;
use Butterfly\Component\DI\Tests\Stubs\ServiceStub;
use Butterfly\Component\DI\Tests\Stubs\ServiceStubWithMagicSetter;
use Butterfly\Component\DI\Tests\Stubs\StaticTriggerService;
use Butterfly\Component\DI\Tests\Stubs\TriggerService;
use Butterfly\Component\DI\Tests\Stubs\UseTriggerService;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function getDataForTestHasParameter()
    {
        $configuration = array(
            'parameters' => array(
                'parameter1' => 'a',
                'Parameter2' => 'a',
            ),
        );

        return array(
            array($configuration, 'parameter1', true, 'has existing parameter - ok'),
            array($configuration, 'undefined_parameter', false, 'has unexisting parameter - fail'),

            array($configuration, 'Parameter1', false, 'search case sensitive - fail'),
            array($configuration, 'Parameter2', true, 'search case sensitive - ok'),
            array($configuration, 'parameter2', false, 'search case sensitive - fail'),
        );
    }

    /**
     * @dataProvider getDataForTestHasParameter
     *
     * @param array $configuration
     * @param $parameterName
     * @param $expectedResult
     * @param $caseMessage
     */
    public function testHasParameter(array $configuration, $parameterName, $expectedResult, $caseMessage)
    {
        $container = new Container($configuration);

        $this->assertEquals($expectedResult, $container->hasParameter($parameterName), $caseMessage);
    }

    public function getDataForTestGetParameter()
    {
        $configuration = array(
            'parameters' => array(
                'parameter1' => 'a',
                'Parameter1' => 'A',
            ),
        );

        return array(
            array($configuration, 'parameter1', 'a', 'get parameter - ok'),
            array($configuration, 'Parameter1', 'A', 'get parameter case sensitive - ok'),
        );
    }

    /**
     * @dataProvider getDataForTestGetParameter
     *
     * @param array $configuration
     * @param $parameterName
     * @param $expectedValue
     * @param $caseMessage
     */
    public function testGetParameter(array $configuration, $parameterName, $expectedValue, $caseMessage)
    {
        $container = new Container($configuration);

        $this->assertEquals($expectedValue, $container->getParameter($parameterName), $caseMessage);
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\UndefinedParameterException
     */
    public function testGetParameterIfNoParameter()
    {
        $configuration = array(
            'parameters' => array(
                'parameter1' => 'a',
            ),
        );
        $container     = new Container($configuration);

        $container->getParameter('undefined_parameter');
    }

    public function getDataForTestHasService()
    {
        $configuration = array(
            'services' => array(
                'service.simple' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
                'SERVICE.SIMPLE2' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
            ),
        );

        return array(
            array($configuration, 'service.simple', true, 'has existing service - ok'),
            array($configuration, 'undefined_service', false, 'has unexisting service - fail'),

            array($configuration, 'SERVICE.SIMPLE', false, 'search case sensitive - fail'),
            array($configuration, 'SERVICE.SIMPLE2', true, 'search case sensitive - ok'),
            array($configuration, 'service.simple2', false, 'search case sensitive - fail'),
        );
    }

    /**
     * @dataProvider getDataForTestHasService
     *
     * @param array $configuration
     * @param $serviceName
     * @param $expectedResult
     * @param $caseMessage
     */
    public function testHasService(array $configuration, $serviceName, $expectedResult, $caseMessage)
    {
        $container = new Container($configuration);

        $this->assertEquals($expectedResult, $container->hasService($serviceName), $caseMessage);
    }

    public function testHasServiceContainerService()
    {
        $configuration = array();
        $container     = new Container($configuration);

        $this->assertTrue($container->hasService(Container::SERVICE_CONTAINER_ID));
    }

    public function testGetServiceContainerService()
    {
        $configuration = array();
        $container     = new Container($configuration);

        $service = $container->getService(Container::SERVICE_CONTAINER_ID);

        $this->assertInstanceOf('\Butterfly\Component\DI\Container', $service);
    }

    public function testGetServiceWithCaseSensitive()
    {
        $configuration = array(
            'services' => array(
                'Service.simple' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
            ),
        );
        $container = new Container($configuration);

        $service = $container->getService('Service.simple');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service);
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\UndefinedServiceException
     */
    public function testGetServiceIfNoService()
    {
        $configuration = array();
        $container     = new Container($configuration);

        $container->getService('undefined_service');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testGetServiceIfIncorrectConfiguration()
    {
        $configuration = array(
            'services' => array(
                'service.incorrect' => array(
                    'arguments' => array(1, 2)
                ),
            ),
        );
        $container     = new Container($configuration);

        $container->getService('service.incorrect');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testGetServiceIfNoClassService()
    {
        $configuration = array(
            'services' => array(
                'undefined_class_service' => array(
                    'class' => 'UndefinedClass',
                ),
            ),
        );
        $container     = new Container($configuration);

        $container->getService('undefined_class_service');
    }

    public function testGetServiceThroughStaticFactoryMethod()
    {
        $configuration = array(
            'services' => array(
                'service.static_factory_output' => array(
                    'factoryStaticMethod' => array(
                        'Butterfly\Component\DI\Tests\Stubs\FactoryService', 'createInstance'
                    ),
                    'arguments'           => array(1, 2),
                ),
            ),
        );
        $container     = new Container($configuration);

        /** @var FactoryOutputService $service */
        $service = $container->getService('service.static_factory_output');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    public function testGetServiceThroughFactory()
    {
        $configuration = array(
            'services' => array(
                'service.factory'        => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\FactoryService',
                ),
                'service.factory_output' => array(
                    'factoryMethod' => array('@service.factory', 'create'),
                    'arguments'     => array(1, 2),
                ),
            ),
        );
        $container     = new Container($configuration);

        /** @var FactoryOutputService $service */
        $service = $container->getService('service.factory_output');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    public function testGetServiceWithSingletonScope()
    {
        ServiceInstanceCounter::$countCreateInstances = 0;
        $configuration                                = array(
            'services' => array(
                'service.scope.singleton' => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceInstanceCounter',
                    'scope' => Container::SCOPE_SINGLETON,
                ),
            ),
        );
        $container                                    = new Container($configuration);

        /** @var ServiceInstanceCounter $service */
        $container->getService('service.scope.singleton');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);

        /** @var ServiceInstanceCounter $service2 */
        $container->getService('service.scope.singleton');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);
    }

    public function testGetServiceWithFactoryScope()
    {
        ServiceInstanceCounter::$countCreateInstances = 0;
        $configuration                                = array(
            'services' => array(
                'service.scope.factory' => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceInstanceCounter',
                    'scope' => Container::SCOPE_FACTORY,
                ),
            ),
        );
        $container                                    = new Container($configuration);

        $container->getService('service.scope.factory');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);

        $container->getService('service.scope.factory');

        $this->assertEquals(2, ServiceInstanceCounter::$countCreateInstances);
    }

    public function testGetServiceWithPrototypeScope()
    {
        ServiceInstanceCounter::$countCreateInstances = 0;
        ServiceInstanceCounter::$countCloneInstances  = 0;
        $configuration                                = array(
            'services' => array(
                'service.scope.prototype' => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceInstanceCounter',
                    'scope' => Container::SCOPE_PROTOTYPE,
                ),
            ),
        );
        $container                                    = new Container($configuration);

        $container->getService('service.scope.prototype');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);
        $this->assertEquals(0, ServiceInstanceCounter::$countCloneInstances);

        $container->getService('service.scope.prototype');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);
        $this->assertEquals(1, ServiceInstanceCounter::$countCloneInstances);
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testGetServiceWithUndefinedScope()
    {
        $configuration = array(
            'services' => array(
                'service.scope.undefined' => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceInstanceCounter',
                    'scope' => 'undefined_scope',
                ),
            ),
        );
        $container     = new Container($configuration);

        $container->getService('service.scope.undefined');
    }

    public function testConstructorInjection()
    {
        $configuration = array(
            'services' => array(
                'service.simple'                => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
                'service.constructor_injection' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                    'arguments' => array('@service.simple')
                ),
            ),
        );
        $container     = new Container($configuration);

        /** @var ComplexServiceStub $service */
        $service = $container->getService('service.constructor_injection');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testSetterInjection()
    {
        $configuration = array(
            'services' => array(
                'service.simple'           => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
                'service.setter_injection' => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                    'calls' => array(
                        array('setInternalService', array('@service.simple')),
                    )
                ),
            ),
        );
        $container     = new Container($configuration);

        /** @var ComplexServiceStub $service */
        $service = $container->getService('service.setter_injection');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testMagicSetterInjection()
    {
        $configuration = array(
            'services' => array(
                'service.magic_setter' => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStubWithMagicSetter',
                    'calls' => array(
                        array('setA', array(1), true),
                    )
                ),
            ),
        );
        $container     = new Container($configuration);

        /** @var ServiceStubWithMagicSetter $service */
        $service = $container->getService('service.magic_setter');

        $this->assertEquals(1, $service->getA());
    }

    public function testPropertyInjection()
    {
        $configuration = array(
            'services' => array(
                'service.simple'             => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
                'service.property_injection' => array(
                    'class'      => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                    'properties' => array(
                        'internalService' => '@service.simple',
                    )
                ),
            ),
        );
        $container     = new Container($configuration);

        /** @var ComplexServiceStub $service */
        $service = $container->getService('service.property_injection');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testPrivatePropertyInjection()
    {
        $configuration = array(
            'services' => array(
                'service.simple'                     => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
                'service.private_property_injection' => array(
                    'class'      => 'Butterfly\Component\DI\Tests\Stubs\PrivatePropertyServiceStub',
                    'properties' => array(
                        'internalService' => '@service.simple',
                    )
                ),
            ),
        );
        $container     = new Container($configuration);

        /** @var PrivatePropertyServiceStub $service */
        $service = $container->getService('service.private_property_injection');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function getDataForTestHasTag()
    {
        $configuration = array(
            'tags' => array(
                'tag1' => array('service.simple', 'service.factory_output'),
                'Tag2' => array('service.simple', 'service.factory_output'),
            ),
        );

        return array(
            array($configuration, 'tag1', true, 'has tag - ok'),
            array($configuration, 'undefined_tag', false, 'has tag - fail'),

            array($configuration, 'Tag1', false, 'search case sensitive - fail'),
            array($configuration, 'Tag2', true, 'search case sensitive - ok'),
            array($configuration, 'tag2', false, 'search case sensitive - fail'),
        );
    }

    /**
     * @dataProvider getDataForTestHasTag
     *
     * @param array $configuration
     * @param $tagName
     * @param $expectedResult
     * @param $caseMessage
     */
    public function testHasTag(array $configuration, $tagName, $expectedResult, $caseMessage)
    {
        $container     = new Container($configuration);

        $this->assertEquals($expectedResult, $container->hasTag($tagName), $caseMessage);
    }

    public function testGetTagList()
    {
        $configuration = array(
            'tags' => array(
                'tag1' => array('service.simple', 'service.factory_output')
            ),
        );
        $container     = new Container($configuration);

        $this->assertEquals(array('tag1'), $container->getTagsList());
    }

    public function testGetServicesByTag()
    {
        $configuration = array(
            'services' => array(
                'service.simple'         => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
                'service.factory'        => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\FactoryService',
                ),
                'service.factory_output' => array(
                    'factoryMethod' => array('@service.factory', 'create'),
                    'arguments'     => array(1, 2),
                ),
            ),
            'tags'     => array(
                'tag1' => array('service.simple', 'service.factory_output')
            ),
        );
        $container     = new Container($configuration);

        $servicesByTag = $container->getServicesByTag('tag1');

        $this->assertCount(2, $servicesByTag);
    }

    public function testGetServicesByTagWithCaseSensitive()
    {
        $configuration = array(
            'services' => array(
                'service.simple'         => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
                'service.factory'        => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\FactoryService',
                ),
                'service.factory_output' => array(
                    'factoryMethod' => array('@service.factory', 'create'),
                    'arguments'     => array(1, 2),
                ),
            ),
            'tags'     => array(
                'tag1' => array('service.simple', 'service.factory_output'),
                'Tag1' => array('service.simple')
            ),
        );
        $container     = new Container($configuration);

        $servicesByTag = $container->getServicesByTag('Tag1');

        $this->assertCount(1, $servicesByTag);
    }

    public function testGetServicesIdsByTag()
    {
        $configuration = array(
            'services' => array(
                'service.simple'         => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
                'service.factory'        => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\FactoryService',
                ),
                'service.factory_output' => array(
                    'factoryMethod' => array('@service.factory', 'create'),
                    'arguments'     => array(1, 2),
                ),
            ),
            'tags'     => array(
                'tag1' => array('service.simple', 'service.factory_output')
            ),
        );
        $container     = new Container($configuration);

        $servicesByTag = $container->getServicesIdsByTag('tag1');

        $this->assertEquals(array('service.simple', 'service.factory_output'), $servicesByTag);
    }

    public function testGetServicesByTagIfNoTag()
    {
        $configuration = array();
        $container     = new Container($configuration);

        $this->assertCount(0, $container->getServicesByTag('undefined_tag'));
    }

    public function testTagDependency()
    {
        $configuration = array(
            'services' => array(
                'service.simple'           => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
                'service.factory'          => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\FactoryService',
                ),
                'service.factory_output'   => array(
                    'factoryMethod' => array('@service.factory', 'create'),
                    'arguments'     => array(1, 2),
                ),
                'service.tag_dependencies' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\FactoryOutputService',
                    'arguments' => array('1', '#tag1'),
                ),
            ),
            'tags'     => array(
                'tag1' => array('service.simple', 'service.factory_output')
            ),
        );
        $container     = new Container($configuration);

        /** @var FactoryOutputService $service */
        $service = $container->getService('service.tag_dependencies');

        $this->assertCount(2, $service->getB());
    }

    public function testTagDependencyIfUndefinedTag()
    {
        $configuration = array(
            'services' => array(
                'service.tag_dependencies.undefined_tag' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\FactoryOutputService',
                    'arguments' => array('1', '#tag123'),
                ),
            ),
        );
        $container     = new Container($configuration);

        $service = $container->getService('service.tag_dependencies.undefined_tag');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\FactoryOutputService', $service);
    }

    public function testHasServiceByAlias()
    {
        $configuration = array(
            'services' => array(
                'service.simple' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
            ),
            'aliases'  => array(
                'service.simple.alias' => 'service.simple',
            ),
        );
        $container     = new Container($configuration);

        $this->assertTrue($container->hasService('service.simple.alias'));
    }

    public function testGetServiceByAlias()
    {
        $configuration = array(
            'services' => array(
                'service.simple' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(1, 2)
                ),
            ),
            'aliases'  => array(
                'service.simple.alias' => 'service.simple',
            ),
        );
        $container     = new Container($configuration);

        /** @var ServiceStub $service */
        $service = $container->getService('service.simple.alias');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service);
    }

    public function testHasInterfaceByAlias()
    {
        $configuration = array(
            'interfaces' => array(
                'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware' => 'service.foo'
            ),
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
                ),
            ),
            'interfaces_aliases' => array(
                'interface.alias' => 'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware',
            ),
        );

        $container = new Container($configuration);

        $this->assertTrue($container->hasInterface('interface.alias'));
        $this->assertFalse($container->hasInterface('interface.undefined_alias'));
    }

    public function testGetInterfaceByAlias()
    {
        $configuration = array(
            'interfaces' => array(
                'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware' => 'service.foo'
            ),
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
                ),
            ),
            'interfaces_aliases' => array(
                'interface.alias' => 'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware',
            ),
        );

        $container = new Container($configuration);

        $this->assertInstanceOf('Butterfly\Component\DI\Tests\Stubs\ServiceFoo', $container->getInterface('interface.alias'));
    }

    public function testTriggers()
    {
        $configuration = array(
            'services' => array(
                'service.trigger'     => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\TriggerService',
                    'arguments' => array('initial'),
                ),
                'service.use_trigger' => array(
                    'class'        => 'Butterfly\Component\DI\Tests\Stubs\UseTriggerService',
                    'arguments'    => array('@service.trigger'),
                    'preTriggers'  => array(
                        array('service' => '@service.trigger', 'method' => 'setA', 'arguments' => array('pre')),
                    ),
                    'postTriggers' => array(
                        array('service' => '@service.trigger', 'method' => 'setA', 'arguments' => array('post')),
                    ),
                ),
            ),
        );
        $container     = new Container($configuration);

        /** @var TriggerService $triggerService */
        $triggerService = $container->getService('service.trigger');

        /** @var UseTriggerService $useTriggerService */
        $useTriggerService = $container->getService('service.use_trigger');

        $this->assertEquals('pre', $useTriggerService->getPreA());
        $this->assertEquals('post', $triggerService->getA());
    }

    public function testStaticTriggers()
    {
        $configuration = array(
            'services' => array(
                'service.use_static_trigger' => array(
                    'class'        => 'Butterfly\Component\DI\Tests\Stubs\UseStaticTriggerService',
                    'preTriggers'  => array(
                        array(
                            'class'     => 'Butterfly\Component\DI\Tests\Stubs\StaticTriggerService',
                            'method'    => 'setA',
                            'arguments' => array('pre')
                        ),
                    ),
                    'postTriggers' => array(
                        array(
                            'class'     => 'Butterfly\Component\DI\Tests\Stubs\StaticTriggerService',
                            'method'    => 'setA',
                            'arguments' => array('post')
                        ),
                    ),
                ),
            ),
        );
        $container     = new Container($configuration);

        StaticTriggerService::setA('initial');

        /** @var UseTriggerService $useTriggerService */
        $useTriggerService = $container->getService('service.use_static_trigger');

        $this->assertEquals('pre', $useTriggerService->getPreA());
        $this->assertEquals('post', StaticTriggerService::getA());
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testStaticTriggersIfUnexistingClass()
    {
        $configuration = array(
            'services' => array(
                'service.trigger.unexists_class' => array(
                    'class'       => 'Butterfly\Component\DI\Tests\Stubs\UseStaticTriggerService',
                    'preTriggers' => array(
                        array('class' => 'UnexistsClass', 'method' => 'setA', 'arguments' => array('pre')),
                    ),
                ),
            ),
        );
        $container     = new Container($configuration);

        $container->getService('service.trigger.unexists_class');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testStaticTriggersIfUnexistingMethod()
    {
        $configuration = array(
            'services' => array(
                'service.trigger.unexists_method' => array(
                    'class'       => 'Butterfly\Component\DI\Tests\Stubs\UseStaticTriggerService',
                    'preTriggers' => array(
                        array(
                            'class'  => 'Butterfly\Component\DI\Tests\Stubs\StaticTriggerService',
                            'method' => 'unexists_method', 'arguments' => array('pre')
                        ),
                    ),
                ),
            ),
        );
        $container     = new Container($configuration);

        $container->getService('service.trigger.unexists_method');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testGetServiceIfIncorrectTriggerType()
    {
        $configuration = array(
            'services' => array(
                'service.incorrect_trigger_type' => array(
                    'class'       => 'Butterfly\Component\DI\Tests\Stubs\UseStaticTriggerService',
                    'preTriggers' => array(
                        array('method' => 'setA', 'arguments' => array('pre')),
                    ),
                ),
            ),
        );
        $container     = new Container($configuration);

        $container->getService('service.incorrect_trigger_type');
    }

    public function testUseSyntheticService()
    {
        $configuration = array(
            'services' => array(
                'service.synthetic'                        => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'scope' => 'synthetic'
                ),
                'service.dependence_for_synthetic_service' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                    'arguments' => array('@service.synthetic')
                ),
            ),
        );
        $container     = new Container($configuration);

        $syntheticService = new ServiceStub(1, 2);

        $container->setSyntheticService('service.synthetic', $syntheticService);

        /** @var ComplexServiceStub $service */
        $service = $container->getService('service.dependence_for_synthetic_service');
        $this->assertEquals($syntheticService, $service->getInternalService());
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\UndefinedServiceException
     */
    public function testUseSyntheticServiceWithCaseSensitive()
    {
        $configuration = array(
            'services' => array(
                'service.synthetic'                        => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'scope' => 'synthetic'
                ),
            ),
        );
        $container     = new Container($configuration);

        $syntheticService = new ServiceStub(1, 2);

        $container->setSyntheticService('SERVICE.SYNTHETIC', $syntheticService);
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\IncorrectSyntheticServiceException
     */
    public function testSetSyntheticServiceIfIncorrectClass()
    {
        $configuration = array(
            'services' => array(
                'service.synthetic' => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'scope' => 'synthetic'
                ),
            ),
        );
        $container     = new Container($configuration);

        $container->setSyntheticService('service.synthetic', new ServiceInstanceCounter());
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testSetSyntheticServiceIfSyntheticServiceIsNotFound()
    {
        $configuration = array(
            'services' => array(
                'service.synthetic'                        => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'scope' => 'synthetic'
                ),
                'service.dependence_for_synthetic_service' => array(
                    'class'     => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                    'arguments' => array('@service.synthetic')
                ),
            ),
        );
        $container     = new Container($configuration);

        $container->getService('service.dependence_for_synthetic_service');
    }

    public function testHasInterface()
    {
        $configuration = array(
            'interfaces' => array(
                'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware' => 'service.foo'
            ),
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
                ),
            ),
        );

        $container = new Container($configuration);

        $this->assertTrue($container->hasInterface('Butterfly\Component\DI\Tests\Stubs\IServiceFooAware'));
    }

    public function testHasInterfaceIfNotExists()
    {
        $configuration = array(
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
                ),
            ),
        );

        $container = new Container($configuration);

        $this->assertFalse($container->hasInterface('undefined'));
    }

    public function testGetInterface()
    {
        $configuration = array(
            'interfaces' => array(
                'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware' => 'service.foo'
            ),
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
                ),
            ),
        );

        $container = new Container($configuration);

        $interfaceImplement = $container->getInterface('Butterfly\Component\DI\Tests\Stubs\IServiceFooAware');
        $this->assertInstanceOf('Butterfly\Component\DI\Tests\Stubs\ServiceFoo', $interfaceImplement);
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\UndefinedInterfaceException
     */
    public function testGetInterfaceIfNotExists()
    {
        $configuration = array(
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
                ),
            ),
        );

        $container = new Container($configuration);

        $container->getInterface('undefined');
    }

    public function testGet()
    {
        $configuration = array(
            'interfaces' => array(
                'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware' => 'service.foo'
            ),
            'parameters' => array(
                'parameter1' => 'a',
            ),
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
                ),
            ),
            'tags'     => array(
                'tag1' => array('service.foo')
            ),
        );

        $container = new Container($configuration);

        // get parameter
        $this->assertEquals('a', $container->get('parameter1'));

        // get service
        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceFoo', $container->get('service.foo'));

        // get interface
        $interface = 'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware';
        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceFoo', $container->get($interface));

        // get tag
        $this->assertCount(1, $container->get('#tag1'));
    }

    public function testHas()
    {
        $configuration = array(
            'interfaces' => array(
                'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware' => 'service.foo'
            ),
            'parameters' => array(
                'parameter1' => 'a',
            ),
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
                ),
            ),
            'tags'     => array(
                'tag1' => array('service.foo')
            ),
        );

        $container = new Container($configuration);

        $this->assertTrue($container->has('parameter1'));
        $this->assertTrue($container->has('service.foo'));
        $this->assertTrue($container->has('#tag1'));
        $this->assertTrue($container->has('Butterfly\Component\DI\Tests\Stubs\IServiceFooAware'));
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\UndefinedInstanceException
     */
    public function testGetIfInstanceNotExists()
    {
        $configuration = array();

        $container = new Container($configuration);

        $container->get('undefined');
    }

    public function getDataForTestGetConfig()
    {
        $configuration = array(
            'interfaces' => array(
                'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware' => 'service.foo'
            ),
            'parameters' => array(
                'parameter1' => 'a',
            ),
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
                ),
            ),
            'tags'     => array(
                'tag1' => array('service.foo')
            ),
            'aliases' => array(),
            'interfaces_aliases' => array(),
        );

        return array(
            array($configuration, '%', $configuration, 'path root. case 1 - ok'),
            array($configuration, '%/', $configuration, 'path root. case 2 - ok'),
            array($configuration, '%/tags', array('tag1' => array('service.foo')), 'path of tags - ok'),
            array($configuration, '%parameters/parameter1', 'a', 'path of parameter. case 1 - ok'),
            array($configuration, '%/parameters/parameter1', 'a', 'path of parameter. case 2 - ok'),
            array($configuration, '%/parameters/parameter1/', 'a', 'path of parameter. case 3 - ok'),
        );
    }

    /**
     * @dataProvider getDataForTestGetConfig
     *
     * @param array $configuration
     * @param $path
     * @param $expectedResult
     * @param $caseMessage
     */
    public function testGetConfig(array $configuration, $path, $expectedResult, $caseMessage)
    {
        $container = new Container($configuration);

        $this->assertEquals($expectedResult, $container->get($path), $caseMessage);
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\IncorrectExpressionPathException
     */
    public function testGetConfigIfUndefinedIndex()
    {
        $configuration = array(
            'parameters' => array(
                'parameter1' => 'a',
            ),
        );

        $container = new Container($configuration);

        $container->get('%/parameters/undefined_parameter');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\IncorrectExpressionPathException
     */
    public function testGetConfigIfIncorrectValue()
    {
        $configuration = array(
            'parameters' => array(
                'parameter1' => 'a',
            ),
        );

        $container = new Container($configuration);

        $container->get('%/parameters/parameter1/sub_parameter');
    }

    public function testReflection()
    {
        $configuration = array(
            'parameters' => array(
                'section_of_parameters' => array(
                    'parameter1' => 'a',
                    'parameter2' => 'b',
                ),
            ),
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(
                        '%parameters/section_of_parameters/parameter1',
                        '%services/service.foo'
                    ),
                ),
            ),
        );

        $container = new Container($configuration);

        /** @var ServiceStub $foo */
        $foo = $container->getService('service.foo');

        $this->assertEquals($configuration['parameters']['section_of_parameters']['parameter1'], $foo->getB());
        $this->assertEquals($configuration['services']['service.foo'], $foo->getC());
    }

    public function testGetForConfigurationExpression()
    {
        $configuration = array(
            'parameters' => array(
                'parameterA' => array(
                    'foo' => 1,
                    'bar' => array(
                        'baz' => 3
                    ),
                )
            ),
        );

        $container = new Container($configuration);

        $this->assertEquals(1, $container->get('parameterA/foo'));
        $this->assertEquals(3, $container->get('parameterA/bar/baz'));
    }

    public function testGetForServiceExpression()
    {
        $configuration = array(
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(
                        'b',
                    ),
                    'properties' => array(
                        'a' => 'a'
                    )
                ),
            ),
        );

        $container = new Container($configuration);

        $this->assertEquals('a', $container->get('service.foo/a'));
        $this->assertEquals('b', $container->get('service.foo/b'));
        $this->assertEquals('b', $container->get('service.foo/getB'));
    }

    public function testGetForTagExpression()
    {
        $configuration = array(
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                ),
                'service.bar'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                ),
            ),
            'tags' => array(
                'tag1' => array('service.foo', 'service.bar')
            )
        );

        $container = new Container($configuration);

        $this->assertCount(2, $container->get('#tag1/toArray'));
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\IncorrectExpressionPathException
     */
    public function testGetForExpressionWithError()
    {
        $configuration = array(
            'parameters' => array(
                'parameterA' => array(
                    'foo' => 1,
                )
            ),
        );

        $container = new Container($configuration);

        $container->get('parameterA/bar');
    }

    public function testGetForInnersExpression()
    {
        $configuration = array(
            'services'   => array(
                'service.foo'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => array(
                        '@service.bar',
                    ),
                ),
                'service.bar'   => array(
                    'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                    'properties' => array(
                        'a' => array('baz' => 123),
                    ),
                ),
            ),
        );

        $container = new Container($configuration);

        $this->assertEquals(123, $container->get('service.foo/b/a/baz'));
    }
}
