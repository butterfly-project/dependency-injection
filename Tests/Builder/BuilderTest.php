<?php

namespace Syringe\Component\DI\Tests\Builder;

use Syringe\Component\DI\Builder\Builder;
use Syringe\Component\DI\Builder\ParameterResolver\Resolver;
use Syringe\Component\DI\Builder\ServiceVisitor\ConfigurationValidator;
use Syringe\Component\DI\Builder\ServiceCollector;
use Syringe\Component\DI\Container;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $rightSections = array(
        'class',
        'factoryMethod',
        'factoryStaticMethod',
        'scope',
        'arguments',
        'calls',
        'properties',
        'preTriggers',
        'postTriggers',
        'tags',
        'alias',
        'parent',
    );

    protected $rightScopes = array(
        '',
        Container::SCOPE_SINGLETON,
        Container::SCOPE_FACTORY,
        Container::SCOPE_PROTOTYPE,
        Container::SCOPE_SYNTHETIC,
    );


    protected $configuration = [
        'parameter_string'  => 'a',
        'parameter_string2' => 'b',
        'parameter_array'   => [1, 2, 3],
        'parameter_complex' => '%parameter_string%/%parameter_string2%',
        'services'          => [
            'service.simple'                => [
                'class'      => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                'arguments'  => [1, '2'],
                'properties' => [
                    'a' => [1, 2, 3],
                ],
                'tags'       => ['tag1'],
                'alias'      => 'service.simple.alias',
            ],

            'service.injected_parameters'   => [
                'class'      => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                'arguments'  => ['%parameter_string%', '%parameter_complex%'],
                'properties' => [
                    'a' => '%parameter_array%',
                ],
            ],

            'service.constructor_injection' => [
                'class'     => 'Syringe\Component\DI\Tests\Stubs\ComplexServiceStub',
                'arguments' => ['@service.simple'],
            ],
            'service.setter_injection'      => [
                'class' => 'Syringe\Component\DI\Tests\Stubs\ComplexServiceStub',
                'calls' => [
                    ['setInternalService', ['@service.simple']],
                ],
                'tags'  => ['tag1'],
            ],
            'service.simple.inheritor'      => [
                'class'  => 'Syringe\Component\DI\Tests\Stubs\ServiceStubInheritor',
                'parent' => 'service.simple',
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $additionalConfiguration = [
        'parameter_string'  => 'abz',
        'parameter_string2' => 'b22222',
    ];

    /**
     * @var array
     */
    protected $expectedConfiguration = [
        'parameters' => [
            'parameter_string'  => 'abz',
            'parameter_string2' => 'b22222',
            'parameter_array'   => [1, 2, 3],
            'parameter_complex' => 'abz/b22222',
        ],
        'services'   => [
            'service.simple'                => [
                'class'      => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                'arguments'  => [1, '2'],
                'properties' => [
                    'a' => [1, 2, 3],
                ],
                'tags'       => ['tag1'],
                'alias'      => 'service.simple.alias',
            ],

            'service.injected_parameters'   => [
                'class'      => 'Syringe\Component\DI\Tests\Stubs\ServiceStub',
                'arguments'  => ['abz', 'abz/b22222'],
                'properties' => [
                    'a' => [1, 2, 3],
                ],
            ],

            'service.constructor_injection' => [
                'class'     => 'Syringe\Component\DI\Tests\Stubs\ComplexServiceStub',
                'arguments' => ['@service.simple'],
            ],
            'service.setter_injection'      => [
                'class' => 'Syringe\Component\DI\Tests\Stubs\ComplexServiceStub',
                'calls' => [
                    ['setInternalService', ['@service.simple']],
                ],
                'tags'  => ['tag1'],
            ],
            'service.simple.inheritor'      => [
                'class'      => 'Syringe\Component\DI\Tests\Stubs\ServiceStubInheritor',
                'arguments'  => [1, '2'],
                'properties' => [
                    'a' => [1, 2, 3],
                ],
            ],
        ],
        'tags'       => [
            'tag1' => ['service.simple', 'service.setter_injection'],
        ],
        'aliases'    => [
            'service.simple.alias' => 'service.simple',
        ],
    ];

    public function testBuild()
    {
        $containerBuilder = new Builder();

        $containerBuilder->setResolver(new Resolver());
        $containerBuilder->addServiceVisitor(new ConfigurationValidator($this->rightSections, $this->rightScopes));
        $containerBuilder->addServiceVisitor(new ServiceCollector\ServiceCollector());
        $containerBuilder->addServiceVisitor(new ServiceCollector\TagCollector());
        $containerBuilder->addServiceVisitor(new ServiceCollector\AliasCollector());

        $containerBuilder->addConfiguration($this->configuration);
        $containerBuilder->addConfiguration($this->additionalConfiguration);

        $configuration = $containerBuilder->build();

        $this->assertEquals($this->expectedConfiguration, $configuration);
    }

    public function testDoubleBuild()
    {
        $containerBuilder = new Builder();

        $containerBuilder->setResolver(new Resolver());
        $containerBuilder->addServiceVisitor(new ConfigurationValidator($this->rightSections, $this->rightScopes));
        $containerBuilder->addServiceVisitor(new ServiceCollector\ServiceCollector());
        $containerBuilder->addServiceVisitor(new ServiceCollector\TagCollector());
        $containerBuilder->addServiceVisitor(new ServiceCollector\AliasCollector());

        $containerBuilder->addConfiguration($this->configuration);
        $containerBuilder->addConfiguration($this->additionalConfiguration);

        $containerBuilder->build();
        $configuration = $containerBuilder->build();

        $this->assertEquals($this->expectedConfiguration, $configuration);
    }
}
