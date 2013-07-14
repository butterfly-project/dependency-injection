<?php

namespace Syringe\Tests;

use Syringe\ContainerConfigurationBuilder;

class ContainerConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $configuration = [
        'parameter_string'  => 'a',
        'parameter_string2' => 'b',
        'parameter_array'   => [1, 2, 3],
        'parameter_complex' => '%parameter_string%/%parameter_string2%',
        'services'   => [
            'service.simple'                => [
                'class'      => 'Syringe\Tests\Stubs\ServiceStub',
                'arguments'  => [1, '2'],
                'properties' => [
                    'a' => [1, 2, 3],
                ],
                'tags'       => ['tag1'],
            ],

            'service.injected_parameters'   => [
                'class'      => 'Syringe\Tests\Stubs\ServiceStub',
                'arguments'  => ['%parameter_string%', '%parameter_complex%'],
                'properties' => [
                    'a' => '%parameter_array%',
                ],
            ],

            'service.constructor_injection' => [
                'class'     => 'Syringe\Tests\Stubs\ComplexServiceStub',
                'arguments' => ['@service.simple'],
            ],
            'service.setter_injection'      => [
                'class' => 'Syringe\Tests\Stubs\ComplexServiceStub',
                'calls' => [
                    ['setInternalService', ['@service.simple']],
                ],
                'tags'  => ['tag1'],
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
                'class'      => 'Syringe\Tests\Stubs\ServiceStub',
                'arguments'  => [1, '2'],
                'properties' => [
                    'a' => [1, 2, 3],
                ],
                'tags'       => ['tag1'],
            ],

            'service.injected_parameters'   => [
                'class'      => 'Syringe\Tests\Stubs\ServiceStub',
                'arguments'  => ['abz', 'abz/b22222'],
                'properties' => [
                    'a' => [1, 2, 3],
                ],
            ],

            'service.constructor_injection' => [
                'class'     => 'Syringe\Tests\Stubs\ComplexServiceStub',
                'arguments' => ['@service.simple'],
            ],
            'service.setter_injection'      => [
                'class' => 'Syringe\Tests\Stubs\ComplexServiceStub',
                'calls' => [
                    ['setInternalService', ['@service.simple']],
                ],
                'tags'  => ['tag1'],
            ],
        ],
        'tags' => [
            'tag1' => ['@service.simple', '@service.setter_injection'],
        ],
    ];

    public function testBuild()
    {
        $containerBuilder = new ContainerConfigurationBuilder($this->configuration);
        $containerBuilder->addConfiguration($this->additionalConfiguration);

        $configuration = $containerBuilder->build();

        $this->assertEquals($this->expectedConfiguration, $configuration);
    }
}
