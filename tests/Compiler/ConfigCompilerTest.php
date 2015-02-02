<?php

namespace Butterfly\Component\DI\Tests\Compiler;

use Butterfly\Component\DI\Compiler\ConfigCompiler;
use Butterfly\Component\DI\Compiler\ParameterResolver\Resolver;
use Butterfly\Component\DI\Compiler\ServiceVisitor\ConfigurationValidator;
use Butterfly\Component\DI\Compiler\ServiceCollector;
use Butterfly\Component\DI\Container;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ConfigCompilerTest extends \PHPUnit_Framework_TestCase
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
        'parameter_string'  => 'abz',
        'parameter_string2' => 'b22222',
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
                'calls' => array(),
                'preTriggers' => array(),
                'postTriggers' => array(),
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

    public function testCompileConfig()
    {
        $compiler = $this->getConfigCompiler();

        $configuration = $compiler->compileConfig($this->configuration);

        $this->assertEquals($this->expectedConfiguration, $configuration);
    }

    public function testDoubleCompileConfig()
    {
        $compiler = $this->getConfigCompiler();

        $compiler->compileConfig($this->configuration);
        $configuration = $compiler->compileConfig($this->configuration);

        $this->assertEquals($this->expectedConfiguration, $configuration);
    }

    public function testEmptyServiceCompileConfig()
    {
        $compiler = $this->getConfigCompiler();

        $compiler->compileConfig(array());
        $configuration = $compiler->compileConfig(array());

        $expectedConfig = array(
            'parameters' => array(),
            'services'   => array(),
            'tags'       => array(),
            'aliases'    => array(),
            'interfaces' => array(),
        );
        $this->assertEquals($expectedConfig, $configuration);
    }

    /**
     * @return ConfigCompiler
     */
    protected function getConfigCompiler()
    {
        return new ConfigCompiler(new Resolver(), array(
            new ConfigurationValidator(),
            new ServiceCollector\ServiceCollector(),
            new ServiceCollector\TagCollector(),
            new ServiceCollector\AliasCollector()
        ));
    }

    public function testCreateInstance()
    {
        $compiler = ConfigCompiler::createInstance();

        $this->assertInstanceOf('\Butterfly\Component\DI\Compiler\ConfigCompiler', $compiler);
    }

    public function testCompile()
    {
        $configuration = ConfigCompiler::compile($this->configuration);

        $this->assertEquals($this->expectedConfiguration, $configuration);
    }
}
