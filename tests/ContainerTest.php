<?php

namespace Butterfly\Component\DI\Tests;

use Butterfly\Component\DI\Container;
use Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub;
use Butterfly\Component\DI\Tests\Stubs\FactoryOutputService;
use Butterfly\Component\DI\Tests\Stubs\PrivatePropertyServiceStub;
use Butterfly\Component\DI\Tests\Stubs\ServiceInstanceCounter;
use Butterfly\Component\DI\Tests\Stubs\ServiceStub;
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
            'parameter1' => 'a',
            'Parameter2' => 'a',
        );

        return array(
            array($configuration, '%parameter1', true, 'has existing parameter - ok'),
            array($configuration, '%undefined_parameter', false, 'has unexisting parameter - fail'),

            array($configuration, '%Parameter1', false, 'search case sensitive - fail'),
            array($configuration, '%Parameter2', true, 'search case sensitive - ok'),
            array($configuration, '%parameter2', false, 'search case sensitive - fail'),
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

        $this->assertEquals($expectedResult, $container->has($parameterName), $caseMessage);
    }

    public function getDataForTestGetParameter()
    {
        $configuration = array(
            'parameter1' => 'a',
            'Parameter1' => 'A',
        );

        return array(
            array($configuration, '%parameter1', 'a', 'get parameter - ok'),
            array($configuration, '%Parameter1', 'A', 'get parameter case sensitive - ok'),
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

        $this->assertEquals($expectedValue, $container->get($parameterName), $caseMessage);
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\IncorrectExpressionPathException
     */
    public function testGetParameterIfNoParameter()
    {
        $configuration = array(
            'parameter1' => 'a',
        );
        $container     = new Container($configuration);

        $container->get('%undefined_parameter');
    }

    public function getDataForTestHasService()
    {
        $configuration = array(
            'service.simple' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments' => array(1, 2)
            ),
            'SERVICE.SIMPLE2' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments' => array(1, 2)
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

        $this->assertEquals($expectedResult, $container->has($serviceName), $caseMessage);
    }

    public function testHasServiceContainerService()
    {
        $configuration = array();
        $container     = new Container($configuration);

        $this->assertTrue($container->has(Container::SERVICE_CONTAINER_ID));
    }

    public function testGetServiceContainerService()
    {
        $configuration = array();
        $container     = new Container($configuration);

        $service = $container->get(Container::SERVICE_CONTAINER_ID);

        $this->assertInstanceOf('\Butterfly\Component\DI\Container', $service);
    }

    public function testGetServiceWithCaseSensitive()
    {
        $configuration = array(
            'Service.simple' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments' => array(1, 2)
            ),
        );
        $container = new Container($configuration);

        $service = $container->get('Service.simple');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service);
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\UndefinedInstanceException
     */
    public function testGetServiceIfNoService()
    {
        $configuration = array();
        $container     = new Container($configuration);

        $container->get('undefined_service');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testGetServiceIfIncorrectConfiguration()
    {
        $configuration = array(
            'service.incorrect' => array(
                'arguments' => array(1, 2)
            ),
        );
        $container     = new Container($configuration);

        $container->get('service.incorrect');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testGetServiceIfNoClassService()
    {
        $configuration = array(
            'undefined_class_service' => array(
                'class' => 'UndefinedClass',
            ),
        );
        $container     = new Container($configuration);

        $container->get('undefined_class_service');
    }

    public function testGetServiceThroughStaticFactoryMethod()
    {
        $configuration = array(
            'service.static_factory_output' => array(
                'factoryStaticMethod' => array(
                    'Butterfly\Component\DI\Tests\Stubs\FactoryService', 'createInstance'
                ),
                'arguments'           => array(1, 2),
            ),
        );
        $container     = new Container($configuration);

        /** @var FactoryOutputService $service */
        $service = $container->get('service.static_factory_output');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    public function testGetServiceThroughFactory()
    {
        $configuration = array(
            'service.factory'        => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\FactoryService',
            ),
            'service.factory_output' => array(
                'factoryMethod' => array('@service.factory', 'create'),
                'arguments'     => array(1, 2),
            ),
        );
        $container     = new Container($configuration);

        /** @var FactoryOutputService $service */
        $service = $container->get('service.factory_output');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    public function testGetServiceWithSingletonScope()
    {
        ServiceInstanceCounter::$countCreateInstances = 0;
        $configuration                                = array(
            'service.scope.singleton' => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceInstanceCounter',
                'scope' => Container::SCOPE_SINGLETON,
            ),
        );
        $container                                    = new Container($configuration);

        /** @var ServiceInstanceCounter $service */
        $container->get('service.scope.singleton');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);

        /** @var ServiceInstanceCounter $service2 */
        $container->get('service.scope.singleton');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);
    }

    public function testGetServiceWithFactoryScope()
    {
        ServiceInstanceCounter::$countCreateInstances = 0;
        $configuration                                = array(
            'service.scope.factory' => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceInstanceCounter',
                'scope' => Container::SCOPE_FACTORY,
            ),
        );
        $container                                    = new Container($configuration);

        $container->get('service.scope.factory');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);

        $container->get('service.scope.factory');

        $this->assertEquals(2, ServiceInstanceCounter::$countCreateInstances);
    }

    public function testGetServiceWithPrototypeScope()
    {
        ServiceInstanceCounter::$countCreateInstances = 0;
        ServiceInstanceCounter::$countCloneInstances  = 0;
        $configuration                                = array(
            'service.scope.prototype' => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceInstanceCounter',
                'scope' => Container::SCOPE_PROTOTYPE,
            ),
        );
        $container                                    = new Container($configuration);

        $container->get('service.scope.prototype');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);
        $this->assertEquals(0, ServiceInstanceCounter::$countCloneInstances);

        $container->get('service.scope.prototype');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);
        $this->assertEquals(1, ServiceInstanceCounter::$countCloneInstances);
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testGetServiceWithUndefinedScope()
    {
        $configuration = array(
            'service.scope.undefined' => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceInstanceCounter',
                'scope' => 'undefined_scope',
            ),
        );
        $container     = new Container($configuration);

        $container->get('service.scope.undefined');
    }

    public function testConstructorInjection()
    {
        $configuration = array(
            'service.simple'                => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments' => array(1, 2)
            ),
            'service.constructor_injection' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                'arguments' => array('@service.simple')
            ),
        );
        $container     = new Container($configuration);

        /** @var ComplexServiceStub $service */
        $service = $container->get('service.constructor_injection');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testSetterInjection()
    {
        $configuration = array(
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
        );
        $container     = new Container($configuration);

        /** @var ComplexServiceStub $service */
        $service = $container->get('service.setter_injection');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testPropertyInjection()
    {
        $configuration = array(
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
        );
        $container     = new Container($configuration);

        /** @var ComplexServiceStub $service */
        $service = $container->get('service.property_injection');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testPrivatePropertyInjection()
    {
        $configuration = array(
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
        );
        $container     = new Container($configuration);

        /** @var PrivatePropertyServiceStub $service */
        $service = $container->get('service.private_property_injection');

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
            'tags'     => array(
                'tag1' => array('service.simple', 'service.factory_output'),
                'Tag1' => array('service.simple')
            ),
        );
        $container     = new Container($configuration);

        $servicesByTag = $container->getServicesByTag('Tag1');

        $this->assertCount(1, $servicesByTag);
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
            'tags'     => array(
                'tag1' => array('service.simple', 'service.factory_output')
            ),
        );
        $container     = new Container($configuration);

        /** @var FactoryOutputService $service */
        $service = $container->get('service.tag_dependencies');

        $this->assertCount(2, $service->getB());
    }

    public function testTagDependencyIfUndefinedTag()
    {
        $configuration = array(
            'service.tag_dependencies.undefined_tag' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\FactoryOutputService',
                'arguments' => array('1', '#tag123'),
            ),
        );
        $container     = new Container($configuration);

        $service = $container->get('service.tag_dependencies.undefined_tag');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\FactoryOutputService', $service);
    }

    public function testTriggers()
    {
        $configuration = array(
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
        );
        $container     = new Container($configuration);

        /** @var TriggerService $triggerService */
        $triggerService = $container->get('service.trigger');

        /** @var UseTriggerService $useTriggerService */
        $useTriggerService = $container->get('service.use_trigger');

        $this->assertEquals('pre', $useTriggerService->getPreA());
        $this->assertEquals('post', $triggerService->getA());
    }

    public function testStaticTriggers()
    {
        $configuration = array(
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
        );
        $container     = new Container($configuration);

        StaticTriggerService::setA('initial');

        /** @var UseTriggerService $useTriggerService */
        $useTriggerService = $container->get('service.use_static_trigger');

        $this->assertEquals('pre', $useTriggerService->getPreA());
        $this->assertEquals('post', StaticTriggerService::getA());
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testStaticTriggersIfUnexistingClass()
    {
        $configuration = array(
            'service.trigger.unexists_class' => array(
                'class'       => 'Butterfly\Component\DI\Tests\Stubs\UseStaticTriggerService',
                'preTriggers' => array(
                    array('class' => 'UnexistsClass', 'method' => 'setA', 'arguments' => array('pre')),
                ),
            ),
        );
        $container     = new Container($configuration);

        $container->get('service.trigger.unexists_class');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testStaticTriggersIfUnexistingMethod()
    {
        $configuration = array(
            'service.trigger.unexists_method' => array(
                'class'       => 'Butterfly\Component\DI\Tests\Stubs\UseStaticTriggerService',
                'preTriggers' => array(
                    array(
                        'class'  => 'Butterfly\Component\DI\Tests\Stubs\StaticTriggerService',
                        'method' => 'unexists_method', 'arguments' => array('pre')
                    ),
                ),
            ),
        );
        $container     = new Container($configuration);

        $container->get('service.trigger.unexists_method');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testGetServiceIfIncorrectTriggerType()
    {
        $configuration = array(
            'service.incorrect_trigger_type' => array(
                'class'       => 'Butterfly\Component\DI\Tests\Stubs\UseStaticTriggerService',
                'preTriggers' => array(
                    array('method' => 'setA', 'arguments' => array('pre')),
                ),
            ),
        );
        $container     = new Container($configuration);

        $container->get('service.incorrect_trigger_type');
    }

    public function testUseSyntheticService()
    {
        $configuration = array(
            'service.synthetic'                        => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'scope' => 'synthetic'
            ),
            'service.dependence_for_synthetic_service' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                'arguments' => array('@service.synthetic')
            ),
        );
        $container     = new Container($configuration);

        $syntheticService = new ServiceStub(1, 2);

        $container->setSyntheticService('service.synthetic', $syntheticService);

        /** @var ComplexServiceStub $service */
        $service = $container->get('service.dependence_for_synthetic_service');
        $this->assertEquals($syntheticService, $service->getInternalService());
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\UndefinedInstanceException
     */
    public function testUseSyntheticServiceWithCaseSensitive()
    {
        $configuration = array(
            'service.synthetic'                        => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'scope' => 'synthetic'
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
            'service.synthetic' => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'scope' => 'synthetic'
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
            'service.synthetic'                        => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'scope' => 'synthetic'
            ),
            'service.dependence_for_synthetic_service' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                'arguments' => array('@service.synthetic')
            ),
        );
        $container     = new Container($configuration);

        $container->get('service.dependence_for_synthetic_service');
    }

    public function testGet()
    {
        $configuration = array(
            'parameter1' => 'a',
            'service.foo'   => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
            ),
            'tags'     => array(
                'tag1' => array('service.foo')
            ),
        );

        $container = new Container($configuration);

        // get parameter
        $this->assertEquals('a', $container->get('%parameter1'));

        // get service
        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceFoo', $container->get('@service.foo'));

        // get tag
        $this->assertCount(1, $container->get('#tag1'));
    }

    public function testHas()
    {
        $configuration = array(
            'parameter1' => 'a',
            'service.foo'   => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
            ),
            'tags'     => array(
                'tag1' => array('service.foo')
            ),
        );

        $container = new Container($configuration);

        $this->assertTrue($container->has('%parameter1'));
        $this->assertTrue($container->has('service.foo'));
        $this->assertTrue($container->has('#tag1'));
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
            'parameter1' => 'a',
            'service.foo'   => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
            ),
            'tags'     => array(
                'tag1' => array('service.foo')
            ),
        );

        return array(
            array($configuration, '%', $configuration, 'path root. case 1 - ok'),
            array($configuration, '%/', $configuration, 'path root. case 2 - ok'),
            array($configuration, '%/tags', array('tag1' => array('service.foo')), 'path of tags - ok'),
            array($configuration, '%parameter1', 'a', 'path of parameter. case 1 - ok'),
            array($configuration, '%/parameter1', 'a', 'path of parameter. case 2 - ok'),
            array($configuration, '%/parameter1/', 'a', 'path of parameter. case 3 - ok'),
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
            'parameter1' => 'a',
        );

        $container = new Container($configuration);

        $container->get('%/undefined_parameter');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\IncorrectExpressionPathException
     */
    public function testGetConfigIfIncorrectValue()
    {
        $configuration = array(
            'parameter1' => 'a',
        );

        $container = new Container($configuration);

        $container->get('%/parameter1/sub_parameter');
    }

    public function testReflection()
    {
        $configuration = array(
            'section_of_parameters' => array(
                'parameter1' => 'a',
                'parameter2' => 'b',
            ),
            'service.foo'   => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments' => array(
                    '%section_of_parameters/parameter1',
                    '%service.foo'
                ),
            ),
        );

        $container = new Container($configuration);

        /** @var ServiceStub $foo */
        $foo = $container->get('service.foo');

        $this->assertEquals($configuration['section_of_parameters']['parameter1'], $foo->getB());
        $this->assertEquals($configuration['service.foo'], $foo->getC());
    }

    public function testGetForConfigurationExpression()
    {
        $configuration = array(
            'parameterA' => array(
                'foo' => 1,
                'bar' => array(
                    'baz' => 3
                ),
            )
        );

        $container = new Container($configuration);

        $this->assertEquals(1, $container->get('%parameterA/foo'));
        $this->assertEquals(3, $container->get('%parameterA/bar/baz'));
    }

    public function testGetForServiceExpression()
    {
        $configuration = array(
            'service.foo'   => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments' => array(
                    'b',
                ),
                'properties' => array(
                    'a' => 'a'
                )
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
            'service.foo'   => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
            ),
            'service.bar'   => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
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
            'parameterA' => array(
                'foo' => 1,
            )
        );

        $container = new Container($configuration);

        $container->get('%parameterA/bar');
    }

    public function testGetForInnersExpression()
    {
        $configuration = array(
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
        );

        $container = new Container($configuration);

        $this->assertEquals(123, $container->get('service.foo/b/a/baz'));
    }
}
