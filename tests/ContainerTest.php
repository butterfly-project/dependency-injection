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
    public function testHasParameter()
    {
        $configuration = array(
            'parameter1' => 'a',
        );

        $container = new Container($configuration);

        $this->assertTrue($container->has('%parameter1'));
    }

    public function testHasParameterIfUndefinedParameter()
    {
        $configuration = array();

        $container = new Container($configuration);

        $this->assertFalse($container->has('%undefined_parameter'));
    }

    public function testHasParameterIfCaseSensitive()
    {
        $configuration = array(
            'parameter1' => 'a',
            'Parameter2' => 'a',
        );

        $container = new Container($configuration);

        $this->assertFalse($container->has('%Parameter1'), 'search case sensitive - fail');
        $this->assertTrue($container->has('%Parameter2'), 'search case sensitive - ok');
        $this->assertFalse($container->has('%parameter2'), 'search case sensitive - fail');
    }

    public function testHasParameterIfRoot()
    {
        $configuration = array(
            'parameter1' => 'a',
            'Parameter2' => 'a',
        );

        $container = new Container($configuration);

        $this->assertTrue($container->has('%'));
    }

    public function testHasParameterIfUseExpression()
    {
        $configuration = array(
            'parameter1' => array(
                'parameter2' => 'a'
            ),
        );

        $container = new Container($configuration);

        $this->assertTrue($container->has('%parameter1/parameter2'), 'expression of parameter. case 1 - ok');
        $this->assertTrue($container->has('%parameter1/parameter2/'), 'expression of parameter. case 1 - ok');
        $this->assertFalse($container->has('%parameter1/undefined_parameter'), 'expression of undefined parameter - fail');
    }

    public function testGetParameter()
    {
        $configuration = array(
            'parameter1' => 'a',
        );

        $container = new Container($configuration);

        $this->assertEquals('a', $container->get('%parameter1'));
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\IncorrectExpressionPathException
     */
    public function testGetParameterIfUndefinedParameter()
    {
        $configuration = array();

        $container = new Container($configuration);

        $container->get('%undefined_parameter');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\IncorrectExpressionPathException
     */
    public function testGetParameterIfUndefinedExpression()
    {
        $configuration = array(
            'parameter' => array()
        );

        $container = new Container($configuration);

        $container->get('%parameter/undefined_parameter');
    }

    public function testGetParameterIfCaseSensitive()
    {
        $configuration = array(
            'parameter1' => 'a',
            'Parameter1' => 'A',
        );

        $container = new Container($configuration);

        $this->assertEquals('a', $container->get('%parameter1'), 'get parameter case sensitive. case 1 - ok');
        $this->assertEquals('A', $container->get('%Parameter1'), 'get parameter case sensitive. case 2 - ok');
    }

    public function testGetParameterIfRoot()
    {
        $configuration = array(
            'parameter1' => 'a',
        );

        $container = new Container($configuration);

        $this->assertEquals($configuration, $container->get('%'));
    }

    public function testGetParameterIfUseExpression()
    {
        $configuration = array(
            'parameter1' => array(
                'parameter2' => 'a'
            ),
        );

        $container = new Container($configuration);

        $this->assertEquals('a', $container->get('%parameter1/parameter2'), 'get parameter if use expression. case 1');
        $this->assertEquals('a', $container->get('%parameter1/parameter2/'), 'get parameter if use expression. case 1');
    }

    public function testHasService()
    {
        $configuration = array(
            'service.simple' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments' => array(1, 2)
            ),
        );

        $container = new Container($configuration);

        $this->assertTrue($container->has('service.simple'));
    }

    public function testHasServiceIfUndefinedService()
    {
        $configuration = array();

        $container = new Container($configuration);

        $this->assertFalse($container->has('undefined_service'));
    }

    public function testHasServiceIfCaseSensitive()
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

        $container = new Container($configuration);

        $this->assertFalse($container->has('SERVICE.SIMPLE'), 'search case sensitive - fail');
        $this->assertTrue($container->has('SERVICE.SIMPLE2'), 'search case sensitive - ok');
        $this->assertFalse($container->has('service.simple2'), 'search case sensitive - fail');
    }

    public function testHasServiceContainerService()
    {
        $configuration = array();
        $container     = new Container($configuration);

        $this->assertTrue($container->has('@'));
    }

    public function testHasServiceIfUseExpression()
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

        $this->assertTrue($container->has('service.foo/a'), 'has service if use expression. case property - ok');
        $this->assertTrue($container->has('service.foo/b'), 'has service if use expression. case with getter - ok');
        $this->assertTrue($container->has('service.foo/getB'), 'has service if use expression. case method - ok');
        $this->assertFalse($container->has('service.foo/undefined'), 'has service if use expression - fail');

        $this->assertTrue($container->has('@service.foo/a'), 'has service if use expression. case property - ok');
        $this->assertTrue($container->has('@service.foo/b'), 'has service if use expression. case with getter - ok');
        $this->assertTrue($container->has('@service.foo/getB'), 'has service if use expression. case method - ok');
        $this->assertFalse($container->has('@service.foo/undefined'), 'has service if use expression - fail');
    }

    public function testGetService()
    {
        $configuration = array(
            'service.simple' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments' => array(1, 2)
            ),
        );
        $container = new Container($configuration);

        $service = $container->get('service.simple');

        $this->assertInstanceOf('\Butterfly\Component\DI\Tests\Stubs\ServiceStub', $service);
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\IncorrectExpressionPathException
     */
    public function testGetServiceIfUndefinedService()
    {
        $configuration = array();

        $container = new Container($configuration);

        $container->get('undefined_service');
    }

    public function testGetServiceIfCaseSensitive()
    {
        $configuration = array(
            'service.simple' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments' => array(1)
            ),
            'SERVICE.SIMPLE' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments' => array(2)
            ),
        );

        $container = new Container($configuration);

        $this->assertEquals(1, $container->get('service.simple')->getB(), 'get instance with case sensitive. case 1');
        $this->assertEquals(2, $container->get('SERVICE.SIMPLE')->getB(), 'get instance with case sensitive. case 2');
    }

    public function testGetServiceContainerService()
    {
        $configuration = array();
        $container     = new Container($configuration);

        $service = $container->get('@');

        $this->assertInstanceOf('\Butterfly\Component\DI\Container', $service);
    }

    public function testGetServiceIfUseExpression()
    {
        $configuration = array(
            'service.foo'   => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments' => array(2),
                'properties' => array(
                    'a' => 1
                )
            ),
        );

        $container = new Container($configuration);

        $this->assertEquals(1, $container->get('service.foo/a'), 'use instance expression. case property - ok');
        $this->assertEquals(2, $container->has('service.foo/b'), 'use instance expression. case with getter - ok');
        $this->assertEquals(2, $container->has('service.foo/getB'), 'use instance expression. case method - ok');

        $this->assertEquals(1, $container->get('@service.foo/a'), 'use instance expression. case property - ok');
        $this->assertEquals(2, $container->has('@service.foo/b'), 'use instance expression. case with getter - ok');
        $this->assertEquals(2, $container->has('@service.foo/getB'), 'use instance expression. case method - ok');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\IncorrectExpressionPathException
     */
    public function testGetServiceIfIncorrectExpression()
    {
        $configuration = array(
            'service.foo'   => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
            ),
        );

        $container = new Container($configuration);

        $container->get('service.foo/undefined');
    }

    /**
     * @expectedException \Butterfly\Component\DI\Exception\BuildServiceException
     */
    public function testGetInstanceIfIncorrectConfiguration()
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
    public function testGetInstanceIfNoClassService()
    {
        $configuration = array(
            'undefined_class_service' => array(
                'class' => 'UndefinedClass',
            ),
        );
        $container     = new Container($configuration);

        $container->get('undefined_class_service');
    }

    public function testGetInstanceThroughStaticFactoryMethod()
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

    public function testGetInstanceThroughFactory()
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

    public function testGetInstanceWithSingletonScope()
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

    public function testGetInstanceWithFactoryScope()
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

    public function testGetInstanceWithPrototypeScope()
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
    public function testGetInstanceWithUndefinedScope()
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
     * @expectedException \Butterfly\Component\DI\Exception\IncorrectExpressionPathException
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

    public function testGetInstancesByTag()
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
            'service.foo'   => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ServiceFoo',
            ),
            'tags'     => array(
                'tag1' => array('service.foo')
            ),
        );

        $container = new Container($configuration);

        $this->assertTrue($container->has('service.foo'));
        $this->assertTrue($container->has('#tag1'));
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
