<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation;

use Butterfly\Component\Annotations\ClassFinder\ClassFinder;
use Butterfly\Component\Annotations\ClassParser;
use Butterfly\Component\Annotations\Parser\PhpDocParser;
use Butterfly\Component\Annotations\Visitor\AnnotationsHandler;
use Butterfly\Component\DI\Compiler\Annotation\AnnotationConfigVisitor;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class AnnotationConfigVisitorTest extends \PHPUnit_Framework_TestCase
{
    public function getDataForTestExtractDiConfiguration()
    {
        $baseNamespace  = 'Butterfly\Component\DI\Tests\Compiler\Annotation\Stub';
        $lowerNamespace = strtolower($baseNamespace);

        return array(

            /**
             * annotations based configuration:
             *
             * @service
             * @arguments
             * @calls
             * @properties
             * @scope
             * @tags
             * @factory
             */

            // Example 1. common annotations
            array(
                'Example 1. common annotations',
                __DIR__ . '/Stub/Annotation/Example1',
                array(
                    'services' => array(
                        "$lowerNamespace\\annotation\\example1\\dira\\innerservice" => array(
                            'class' => "$baseNamespace\\Annotation\\Example1\\DirA\\InnerService",
                        ),
                        "$lowerNamespace\\annotation\\example1\\service"            => array(
                            'class'        => "$baseNamespace\\Annotation\\Example1\\Service",
                            'arguments'    => array(
                                '@service.inner',
                            ),
                            'calls'        => array(
                                array('setParameterA', array('%parameter.a%')),
                                array('setParameterB', array('%parameter.b%')),
                            ),
                            'properties'   => array(
                                'propertyA' => '%parameter_of_property.a%',
                                'propertyB' => '%parameter_of_property.b%',
                            ),
                            'scope'        => 'factory',
                            'tags'         => array('service.tag'),
                            'preTriggers'  => array(
                                array(
                                    'service'   => 'service.trigger',
                                    'method'    => 'beforeCreate',
                                    'arguments' => array('%parameter.a%'),
                                ),
                                array(
                                    'class'     => 'Me\Trigger',
                                    'method'    => 'beforeCreate',
                                    'arguments' => array('%parameter.a%'),
                                ),
                            ),
                            'postTriggers' => array(
                                array(
                                    'service'   => 'service.trigger',
                                    'method'    => 'afterCreate',
                                    'arguments' => array('%parameter.b%'),
                                ),
                                array(
                                    'class'     => 'Me\Trigger',
                                    'method'    => 'afterCreate',
                                    'arguments' => array('%parameter.b%'),
                                ),
                            ),
                        ),
                    ),
                    'aliases'  => array(
                        'service.base' => "$lowerNamespace\\annotation\\example1\\service",
                    ),
                )
            ),

            // Example 2. basic factory methods and static factory methods
            array(
                'Example 2. basic factory methods and static factory methods',
                __DIR__ . '/Stub/Annotation/Example2',
                array(
                    'services' => array(
                        "$lowerNamespace\\annotation\\example2\\service" => array(
                            'class' => "$baseNamespace\\Annotation\\Example2\\Service",
                        ),
                        'service.inner'   => array(
                            'factoryMethod' => array("@$lowerNamespace\\annotation\\example2\\service", 'createInnerService'),
                            'arguments'     => array(),
                        ),
                        'service.inner2'  => array(
                            'factoryMethod' => array("@$lowerNamespace\\annotation\\example2\\service", 'createInnerService2'),
                            'arguments'     => array('%parameter.a%', '%parameter.b%'),
                        ),
                        'service.inner3'  => array(
                            'factoryStaticMethod' => array(
                                "$baseNamespace\\Annotation\\Example2\\Service", 'createInnerService3'
                            ),
                            'arguments'           => array(),
                        ),
                        'service.inner4'  => array(
                            'factoryStaticMethod' => array(
                                "$baseNamespace\\Annotation\\Example2\\Service", 'createInnerService4'
                            ),
                            'arguments'           => array('%parameter.a%', '%parameter.b%'),
                        ),
                    ),
                    'aliases'   => array(
                        'service.factory' => "$lowerNamespace\\annotation\\example2\\service"
                    ),
                )
            ),

            /**
             * autowired configuration:
             *
             * @service
             * @autowired
             */

            // Example 1. not named service
            array(
                'Example 1. not named service',
                __DIR__ . '/Stub/Autowired/Example1',
                array(
                    'services' => array(
                        "$lowerNamespace\\autowired\\example1\\service" => array(
                            'class' => "$baseNamespace\\Autowired\\Example1\\Service",
                        ),
                    ),
                    'aliases' => array(),
                )
            ),

            // Example 2. property for type
            array(
                'Example 2. property for type',
                __DIR__ . '/Stub/Autowired/Example2',
                array(
                    'services' => array(
                        "$lowerNamespace\\autowired\\example2\\dira\\innerservice" => array(
                            'class' => "$baseNamespace\\Autowired\\Example2\\DirA\\InnerService",
                        ),
                        "$lowerNamespace\\autowired\\example2\\service"            => array(
                            'class'      => "$baseNamespace\\Autowired\\Example2\\Service",
                            'properties' => array(
                                'inner' => "$lowerNamespace\\autowired\\example2\\dira\\innerservice",
                            ),
                        ),
                    ),
                    'aliases'  => array(
                        'service.base' => "$lowerNamespace\\autowired\\example2\\service",
                    ),
                )
            ),

            // Example 3. property for autowired value
            array(
                'Example 3. property for autowired value',
                __DIR__ . '/Stub/Autowired/Example3',
                array(
                    'services' => array(
                        "$lowerNamespace\\autowired\\example3\\service" => array(
                            'class'      => "$baseNamespace\\Autowired\\Example3\\Service",
                            'properties' => array(
                                'innerService'  => "service.inner",
                                'innerProperty' => "parameter.inner",
                            ),
                        ),
                    ),
                    'aliases' => array(
                        'service.base' => "$lowerNamespace\\autowired\\example3\\service",
                    ),
                )
            ),

            // Example 4. methods for types
            array(
                'Example 4. methods for types',
                __DIR__ . '/Stub/Autowired/Example4',
                array(
                    'services' => array(
                        "$lowerNamespace\\autowired\\example4\\dira\\innerservice"  => array(
                            'class' => "$baseNamespace\\Autowired\\Example4\\DirA\\InnerService",
                        ),
                        "$lowerNamespace\\autowired\\example4\\dira\\inner2service" => array(
                            'class' => "$baseNamespace\\Autowired\\Example4\\DirA\\Inner2Service",
                        ),
                        "$lowerNamespace\\autowired\\example4\\service"             => array(
                            'class' => "$baseNamespace\\Autowired\\Example4\\Service",
                            'calls' => array(
                                array(
                                    'init', array(
                                    "@$lowerNamespace\\autowired\\example4\\dira\\innerservice",
                                    "@$lowerNamespace\\autowired\\example4\\dira\\inner2service",
                                )
                                ),
                            ),
                        ),
                    ),
                    'aliases'   => array(
                        'service.base' => "$lowerNamespace\\autowired\\example4\\service",
                    ),
                )
            ),

            // Example 5. methods for autowired annotation
            array(
                'Example 5. methods for autowired annotation',
                __DIR__ . '/Stub/Autowired/Example5',
                array(
                    'services' => array(
                        "$lowerNamespace\\autowired\\example5\\dira\\innerservice"  => array(
                            'class' => "$baseNamespace\\Autowired\\Example5\\DirA\\InnerService",
                        ),
                        "$lowerNamespace\\autowired\\example5\\dira\\inner2service" => array(
                            'class' => "$baseNamespace\\Autowired\\Example5\\DirA\\Inner2Service",
                        ),
                        "$lowerNamespace\\autowired\\example5\\service"             => array(
                            'class' => "$baseNamespace\\Autowired\\Example5\\Service",
                            'calls' => array(
                                array(
                                    'init', array(
                                    "@service.inner",
                                    "@service.inner2",
                                    "%parameter.input%",
                                )
                                ),
                            ),
                        ),
                    ),
                    'aliases'  => array(
                        'service.base' => "$lowerNamespace\\autowired\\example5\\service",
                    ),
                )
            ),

            // Example 6. methods for phpDoc types
            array(
                'Example 6. methods for phpDoc types',
                __DIR__ . '/Stub/Autowired/Example6',
                array(
                    'services' => array(
                        "$lowerNamespace\\autowired\\example6\\dira\\innerservice"  => array(
                            'class' => "$baseNamespace\\Autowired\\Example6\\DirA\\InnerService",
                        ),
                        "$lowerNamespace\\autowired\\example6\\dira\\inner2service" => array(
                            'class' => "$baseNamespace\\Autowired\\Example6\\DirA\\Inner2Service",
                        ),
                        "$lowerNamespace\\autowired\\example6\\service"             => array(
                            'class' => "$baseNamespace\\Autowired\\Example6\\Service",
                            'calls' => array(
                                array(
                                    'init', array(
                                    "@$lowerNamespace\\autowired\\example6\\dira\\innerservice",
                                    "@$lowerNamespace\\autowired\\example6\\dira\\inner2service",
                                )
                                ),
                            ),
                        ),
                    ),
                    'aliases'  => array(
                        'service.base' => "$lowerNamespace\\autowired\\example6\\service",
                    ),
                )
            ),

            // Example 7. constructor
            array(
                'Example 7. constructor',
                __DIR__ . '/Stub/Autowired/Example7',
                array(
                    'services' => array(
                        "$lowerNamespace\\autowired\\example7\\dira\\innerservice" => array(
                            'class' => "$baseNamespace\\Autowired\\Example7\\DirA\\InnerService",
                        ),
                        "$lowerNamespace\\autowired\\example7\\service"            => array(
                            'class'     => "$baseNamespace\\Autowired\\Example7\\Service",
                            'arguments' => array(
                                '@service.inner',
                            ),
                        ),
                    ),
                    'aliases' => array(
                        'service.base' => "$lowerNamespace\\autowired\\example7\\service",
                    ),
                )
            ),
        );
    }

    /**
     * @dataProvider getDataForTestExtractDiConfiguration
     *
     * @param string $caseDescription
     * @param string $dirPath
     * @param array $expectedConfig
     */
    public function testExtractDiConfiguration($caseDescription, $dirPath, array $expectedConfig)
    {
        $classParser = new ClassParser(new PhpDocParser());
        $classFinder = new ClassFinder(array('php'));
        $classes     = $classFinder->findClassesInDir($dirPath);

        $annotations = array();
        foreach ($classes as $class) {
            $annotations[$class] = $classParser->parseClass($class);
        }

        $visitor     = new AnnotationConfigVisitor();

        $annotationHandler = new AnnotationsHandler();
        $annotationHandler->addVisitor($visitor);
        $annotationHandler->handle($annotations);

        $diConfig = $visitor->extractDiConfiguration();

        ksort($expectedConfig['services']);
        ksort($diConfig['services']);

        $this->assertEquals($expectedConfig, $diConfig, $caseDescription);
    }
}
