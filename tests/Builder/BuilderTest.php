<?php

namespace Butterfly\Component\DI\Tests\Builder;

use Butterfly\Component\DI\Builder\Builder;
use Butterfly\Component\DI\Builder\ParameterResolver\Resolver;
use Butterfly\Component\DI\Builder\ServiceVisitor\ConfigurationValidator;
use Butterfly\Component\DI\Builder\ServiceCollector;
use Butterfly\Component\DI\Container;

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


    protected $configuration = array(
        'parameter_string'  => 'a',
        'parameter_string2' => 'b',
        'parameter_array'   => array(1, 2, 3),
        'parameter_complex' => '%parameter_string%/%parameter_string2%',
        'interfaces'        => array(
            'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware' => 'service.simple',
        ),
        'services'          => array(
            'service.simple'                => array(
                'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments'  => array(1, '2'),
                'properties' => array(
                    'a' => array(1, 2, 3),
                ),
                'tags'       => array('tag1'),
                'alias'      => 'service.simple.alias',
            ),

            'service.injected_parameters'   => array(
                'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments'  => array('%parameter_string%', '%parameter_complex%'),
                'properties' => array(
                    'a' => '%parameter_array%',
                ),
            ),

            'service.constructor_injection' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                'arguments' => array('@service.simple'),
            ),
            'service.setter_injection'      => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                'calls' => array(
                    array('setInternalService', array('@service.simple')),
                ),
                'tags'  => array('tag1'),
            ),
            'service.simple.inheritor'      => array(
                'class'  => 'Butterfly\Component\DI\Tests\Stubs\ServiceStubInheritor',
                'parent' => 'service.simple',
            ),
        ),
    );

    /**
     * @var array
     */
    protected $additionalConfiguration = array(
        'parameter_string'  => 'abz',
        'parameter_string2' => 'b22222',
    );

    /**
     * @var array
     */
    protected $expectedConfiguration = array(
        'parameters' => array(
            'parameter_string'  => 'abz',
            'parameter_string2' => 'b22222',
            'parameter_array'   => array(1, 2, 3),
            'parameter_complex' => 'abz/b22222',
        ),
        'services'   => array(
            'service.simple'                => array(
                'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments'  => array(1, '2'),
                'properties' => array(
                    'a' => array(1, 2, 3),
                ),
                'tags'       => array('tag1'),
                'alias'      => 'service.simple.alias',
            ),

            'service.injected_parameters'   => array(
                'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStub',
                'arguments'  => array('abz', 'abz/b22222'),
                'properties' => array(
                    'a' => array(1, 2, 3),
                ),
            ),

            'service.constructor_injection' => array(
                'class'     => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                'arguments' => array('@service.simple'),
            ),
            'service.setter_injection'      => array(
                'class' => 'Butterfly\Component\DI\Tests\Stubs\ComplexServiceStub',
                'calls' => array(
                    array('setInternalService', array('@service.simple')),
                ),
                'tags'  => array('tag1'),
            ),
            'service.simple.inheritor'      => array(
                'class'      => 'Butterfly\Component\DI\Tests\Stubs\ServiceStubInheritor',
                'arguments'  => array(1, '2'),
                'properties' => array(
                    'a' => array(1, 2, 3),
                ),
            ),
        ),
        'tags'       => array(
            'tag1' => array('service.simple', 'service.setter_injection'),
        ),
        'aliases'    => array(
            'service.simple.alias' => 'service.simple',
        ),
        'interfaces' => array(
            'Butterfly\Component\DI\Tests\Stubs\IServiceFooAware' => 'service.simple'
        ),
    );

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

    public function testEmptyServiceBuild()
    {
        $containerBuilder = new Builder();

        $containerBuilder->setResolver(new Resolver());
        $containerBuilder->addServiceVisitor(new ConfigurationValidator($this->rightSections, $this->rightScopes));
        $containerBuilder->addServiceVisitor(new ServiceCollector\ServiceCollector());
        $containerBuilder->addServiceVisitor(new ServiceCollector\TagCollector());
        $containerBuilder->addServiceVisitor(new ServiceCollector\AliasCollector());

        $containerBuilder->addConfiguration(array());

        $containerBuilder->build();
        $configuration = $containerBuilder->build();

        $expectedConfig = array(
            'parameters' => array(),
            'services'   => array(),
            'tags'       => array(),
            'aliases'    => array(),
            'interfaces' => array(),
        );
        $this->assertEquals($expectedConfig, $configuration);
    }
}
