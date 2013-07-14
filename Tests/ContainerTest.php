<?php

namespace Syringe\Component\DI\Tests;

use Syringe\Component\DI\Container;
use Syringe\Component\DI\Tests\Stubs\ComplexServiceStub;
use Syringe\Component\DI\Tests\Stubs\FactoryOutputService;
use Syringe\Component\DI\Tests\Stubs\PrivatePropertyServiceStub;
use Syringe\Component\DI\Tests\Stubs\ServiceInstanceCounter;
use Syringe\Component\DI\Tests\Stubs\ServiceStub;
use Syringe\Component\DI\Tests\Stubs\StaticTriggerService;
use Syringe\Component\DI\Tests\Stubs\TriggerService;
use Syringe\Component\DI\Tests\Stubs\UseTriggerService;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function getDataForTestCreateContainerIfIncorrectConfiguration()
    {
        return [
            [[]],
            [['parameters' => [], 'services' => [], 'aliases' => []]],
            [['parameters' => [], 'tags' => [], 'aliases' => []]],
            [['services' => [], 'tags' => [], 'aliases' => []]],
            [['parameters' => [], 'services' => [], 'tags' => []]],
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
        $configuration = [
            'parameters' => [
                'parameter1' => 'a',
            ],
            'services'   => [],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $this->assertTrue($container->hasParameter('parameter1'));
    }

    public function testHasParameterIfNoParameter()
    {
        $configuration = [
            'parameters' => [
                'parameter1' => 'a',
            ],
            'services'   => [],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $this->assertFalse($container->hasParameter('undefined_parameter'));
    }

    public function testGetParameter()
    {
        $configuration = [
            'parameters' => [
                'parameter1' => 'a',
            ],
            'services'   => [],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $this->assertEquals('a', $container->getParameter('parameter1'));
    }

    /**
     * @expectedException \Syringe\Component\DI\Exception\UndefinedParameterException
     */
    public function testGetParameterIfNoParameter()
    {
        $configuration = [
            'parameters' => [
                'parameter1' => 'a',
            ],
            'services'   => [],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $container->getParameter('undefined_parameter');
    }

    public function testHasService()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.simple' => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => [1, 2]
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $this->assertTrue($container->has('service.simple'));
    }

    public function testHasServiceIfNoService()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.simple' => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => [1, 2]
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $this->assertFalse($container->has('undefined_service'));
    }

    public function testHasServiceContainerService()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $this->assertTrue($container->has(Container::SERVICE_CONTAINER_ID));
    }

    public function testGetServiceContainerService()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $service = $container->get(Container::SERVICE_CONTAINER_ID);

        $this->assertInstanceOf('\Syringe\Component\DI\Container', $service);
    }

    /**
     * @expectedException \Syringe\Component\DI\Exception\UndefinedServiceException
     */
    public function testGetServiceIfNoService()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $container->get('undefined_service');
    }

    /**
     * @expectedException \Syringe\Component\DI\Exception\BuildServiceException
     */
    public function testGetServiceIfIncorrectConfiguration()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.incorrect' => [
                    'arguments' => [1, 2]
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $container->get('service.incorrect');
    }

    /**
     * @expectedException \Syringe\Component\DI\Exception\BuildServiceException
     */
    public function testGetServiceIfNoClassService()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'undefined_class_service' => [
                    'class' => 'UndefinedClass',
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $container->get('undefined_class_service');
    }

    public function testGetServiceThroughStaticFactoryMethod()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.static_factory_output' => [
                    'factoryStaticMethod' => ['Syringe\Component\DI\Tests\Stubs\FactoryService', 'createInstance'],
                    'arguments'           => [1, 2],
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        /** @var FactoryOutputService $service */
        $service = $container->get('service.static_factory_output');

        $this->assertInstanceOf('\Syringe\Component\DI\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    public function testGetServiceThroughFactory()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.factory'        => [
                    'class' => 'Syringe\Component\DI\Tests\Stubs\FactoryService',
                ],
                'service.factory_output' => [
                    'factoryMethod' => ['@service.factory', 'create'],
                    'arguments'     => [1, 2],
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        /** @var FactoryOutputService $service */
        $service = $container->get('service.factory_output');

        $this->assertInstanceOf('\Syringe\Component\DI\Tests\Stubs\FactoryOutputService', $service);
        $this->assertEquals(1, $service->getA());
        $this->assertEquals(2, $service->getB());
    }

    public function testGetServiceWithSingletonScope()
    {
        ServiceInstanceCounter::$countCreateInstances = 0;
        $configuration                                = [
            'parameters' => [],
            'services'   => [
                'service.scope.singleton' => [
                    'class' => 'Syringe\Component\DI\Tests\Stubs\ServiceInstanceCounter',
                    'scope' => Container::SCOPE_SINGLETON,
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
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
        $configuration                                = [
            'parameters' => [],
            'services'   => [
                'service.scope.factory' => [
                    'class' => 'Syringe\Component\DI\Tests\Stubs\ServiceInstanceCounter',
                    'scope' => Container::SCOPE_FACTORY,
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
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
        $configuration                                = [
            'parameters' => [],
            'services'   => [
                'service.scope.prototype' => [
                    'class' => 'Syringe\Component\DI\Tests\Stubs\ServiceInstanceCounter',
                    'scope' => Container::SCOPE_PROTOTYPE,
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container                                    = new Container($configuration);

        $container->get('service.scope.prototype');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);
        $this->assertEquals(0, ServiceInstanceCounter::$countCloneInstances);

        $container->get('service.scope.prototype');

        $this->assertEquals(1, ServiceInstanceCounter::$countCreateInstances);
        $this->assertEquals(1, ServiceInstanceCounter::$countCloneInstances);
    }

    /**
     * @expectedException \Syringe\Component\DI\Exception\BuildServiceException
     */
    public function testGetServiceWithUndefinedScope()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.scope.undefined' => [
                    'class' => 'Syringe\Component\DI\Tests\Stubs\ServiceInstanceCounter',
                    'scope' => 'undefined_scope',
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $container->get('service.scope.undefined');
    }

    public function testConstructorInjection()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.simple'                => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => [1, 2]
                ],
                'service.constructor_injection' => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ComplexServiceStub',
                    'arguments' => ['@service.simple']
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        /** @var ComplexServiceStub $service */
        $service = $container->get('service.constructor_injection');

        $this->assertInstanceOf('\Syringe\Component\DI\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testSetterInjection()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.simple'           => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => [1, 2]
                ],
                'service.setter_injection' => [
                    'class' => 'Syringe\Component\DI\Tests\Stubs\ComplexServiceStub',
                    'calls' => [
                        ['setInternalService', ['@service.simple']],
                    ]
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        /** @var ComplexServiceStub $service */
        $service = $container->get('service.setter_injection');

        $this->assertInstanceOf('\Syringe\Component\DI\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testPropertyInjection()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.simple'             => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => [1, 2]
                ],
                'service.property_injection' => [
                    'class'      => 'Syringe\Component\DI\Tests\Stubs\ComplexServiceStub',
                    'properties' => [
                        'internalService' => '@service.simple',
                    ]
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        /** @var ComplexServiceStub $service */
        $service = $container->get('service.property_injection');

        $this->assertInstanceOf('\Syringe\Component\DI\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testPrivatePropertyInjection()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.simple'                     => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => [1, 2]
                ],
                'service.private_property_injection' => [
                    'class'      => 'Syringe\Component\DI\Tests\Stubs\PrivatePropertyServiceStub',
                    'properties' => [
                        'internalService' => '@service.simple',
                    ]
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        /** @var PrivatePropertyServiceStub $service */
        $service = $container->get('service.private_property_injection');

        $this->assertInstanceOf('\Syringe\Component\DI\Tests\Stubs\ServiceStub', $service->getInternalService());
    }

    public function testHasTag()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [],
            'tags'       => [
                'tag1' => ['@service.simple', '@service.factory_output']
            ],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $this->assertTrue($container->hasTag('tag1'));
    }

    public function testHasTagIfNoTag()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $this->assertFalse($container->hasTag('undefined_tag'));
    }

    public function testGetTagList()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [],
            'tags'       => [
                'tag1' => ['@service.simple', '@service.factory_output']
            ],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $this->assertEquals(['tag1'], $container->getTagsList());
    }

    public function testGetServicesByTag()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.simple'         => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => [1, 2]
                ],
                'service.factory'        => [
                    'class' => 'Syringe\Component\DI\Tests\Stubs\FactoryService',
                ],
                'service.factory_output' => [
                    'factoryMethod' => ['@service.factory', 'create'],
                    'arguments'     => [1, 2],
                ],
            ],
            'tags'       => [
                'tag1' => ['@service.simple', '@service.factory_output']
            ],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $servicesByTag = $container->getServicesByTag('tag1');

        $this->assertCount(2, $servicesByTag);
    }

    /**
     * @expectedException \Syringe\Component\DI\Exception\UndefinedTagException
     */
    public function testGetServicesByTagIfNoTag()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $container->getServicesByTag('undefined_tag');
    }

    public function testTagDependency()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.simple'           => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => [1, 2]
                ],
                'service.factory'          => [
                    'class' => 'Syringe\Component\DI\Tests\Stubs\FactoryService',
                ],
                'service.factory_output'   => [
                    'factoryMethod' => ['@service.factory', 'create'],
                    'arguments'     => [1, 2],
                ],
                'service.tag_dependencies' => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\FactoryOutputService',
                    'arguments' => ['1', '#tag1'],
                ],
            ],
            'tags'       => [
                'tag1' => ['@service.simple', '@service.factory_output']
            ],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        /** @var FactoryOutputService $service */
        $service = $container->get('service.tag_dependencies');

        $this->assertCount(2, $service->getB());
    }

    /**
     * @expectedException \Syringe\Component\DI\Exception\UndefinedTagException
     */
    public function testTagDependencyIfUndefinedTag()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.tag_dependencies.undefined_tag' => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\FactoryOutputService',
                    'arguments' => ['1', '#tag123'],
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $container->get('service.tag_dependencies.undefined_tag');
    }

    public function testHasServiceByAlias()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.simple' => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => [1, 2]
                ],
            ],
            'tags'       => [],
            'aliases'    => [
                'service.simple.alias' => 'service.simple',
            ],
        ];
        $container     = new Container($configuration);

        $this->assertTrue($container->has('service.simple.alias'));
    }

    public function testGetServiceByAlias()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.simple' => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'arguments' => [1, 2]
                ],
            ],
            'tags'       => [],
            'aliases'    => [
                'service.simple.alias' => 'service.simple',
            ],
        ];
        $container     = new Container($configuration);

        /** @var ServiceStub $service */
        $service = $container->get('service.simple.alias');

        $this->assertInstanceOf('\Syringe\Component\DI\Tests\Stubs\ServiceStub', $service);
    }

    public function testTriggers()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.trigger'     => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\TriggerService',
                    'arguments' => ['initial'],
                ],
                'service.use_trigger' => [
                    'class'        => 'Syringe\Component\DI\Tests\Stubs\UseTriggerService',
                    'arguments'    => ['@service.trigger'],
                    'preTriggers'  => [
                        ['service' => '@service.trigger', 'method' => 'setA', 'arguments' => ['pre']],
                    ],
                    'postTriggers' => [
                        ['service' => '@service.trigger', 'method' => 'setA', 'arguments' => ['post']],
                    ],
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
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
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.use_static_trigger' => [
                    'class'        => 'Syringe\Component\DI\Tests\Stubs\UseStaticTriggerService',
                    'preTriggers'  => [
                        [
                            'class'     => 'Syringe\Component\DI\Tests\Stubs\StaticTriggerService', 'method' => 'setA',
                            'arguments' => ['pre']
                        ],
                    ],
                    'postTriggers' => [
                        [
                            'class'     => 'Syringe\Component\DI\Tests\Stubs\StaticTriggerService', 'method' => 'setA',
                            'arguments' => ['post']
                        ],
                    ],
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        StaticTriggerService::setA('initial');

        /** @var UseTriggerService $useTriggerService */
        $useTriggerService = $container->get('service.use_static_trigger');

        $this->assertEquals('pre', $useTriggerService->getPreA());
        $this->assertEquals('post', StaticTriggerService::getA());
    }

    /**
     * @expectedException \Syringe\Component\DI\Exception\BuildServiceException
     */
    public function testStaticTriggersIfUnexistingClass()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.trigger.unexists_class' => [
                    'class'       => 'Syringe\Component\DI\Tests\Stubs\UseStaticTriggerService',
                    'preTriggers' => [
                        ['class' => 'UnexistsClass', 'method' => 'setA', 'arguments' => ['pre']],
                    ],
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $container->get('service.trigger.unexists_class');
    }

    /**
     * @expectedException \Syringe\Component\DI\Exception\BuildServiceException
     */
    public function testStaticTriggersIfUnexistingMethod()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.trigger.unexists_method' => [
                    'class'       => 'Syringe\Component\DI\Tests\Stubs\UseStaticTriggerService',
                    'preTriggers' => [
                        [
                            'class'  => 'Syringe\Component\DI\Tests\Stubs\StaticTriggerService',
                            'method' => 'unexists_method', 'arguments' => ['pre']
                        ],
                    ],
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $container->get('service.trigger.unexists_method');
    }

    /**
     * @expectedException \Syringe\Component\DI\Exception\BuildServiceException
     */
    public function testGetServiceIfIncorrectTriggerType()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.incorrect_trigger_type' => [
                    'class'       => 'Syringe\Component\DI\Tests\Stubs\UseStaticTriggerService',
                    'preTriggers' => [
                        ['method' => 'setA', 'arguments' => ['pre']],
                    ],
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $container->get('service.incorrect_trigger_type');
    }

    public function testUseSyntheticService()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.synthetic'                        => [
                    'class' => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'scope' => 'synthetic'
                ],
                'service.dependence_for_synthetic_service' => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ComplexServiceStub',
                    'arguments' => ['@service.synthetic']
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $syntheticService = new ServiceStub(1, 2);

        $container->setSyntheticService('service.synthetic', $syntheticService);

        /** @var ComplexServiceStub $service */
        $service = $container->get('service.dependence_for_synthetic_service');
        $this->assertEquals($syntheticService, $service->getInternalService());
    }

    /**
     * @expectedException \Syringe\Component\DI\Exception\IncorrectSyntheticServiceException
     */
    public function testSetSyntheticServiceIfIncorrectClass()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.synthetic' => [
                    'class' => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'scope' => 'synthetic'
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $container->setSyntheticService('service.synthetic', new ServiceInstanceCounter());
    }

    /**
     * @expectedException \Syringe\Component\DI\Exception\BuildServiceException
     */
    public function testSetSyntheticServiceIfSyntheticServiceIsNotFound()
    {
        $configuration = [
            'parameters' => [],
            'services'   => [
                'service.synthetic'                        => [
                    'class' => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                    'scope' => 'synthetic'
                ],
                'service.dependence_for_synthetic_service' => [
                    'class'     => 'Syringe\Component\DI\Tests\Stubs\ComplexServiceStub',
                    'arguments' => ['@service.synthetic']
                ],
            ],
            'tags'       => [],
            'aliases'    => [],
        ];
        $container     = new Container($configuration);

        $container->get('service.dependence_for_synthetic_service');
    }
}
