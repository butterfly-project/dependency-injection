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
             * @alias
             * @tags
             * @factory
             */

            // Example 1. common annotations
            array(__DIR__ . '/Stub/Annotation/Example1', array('services' => array(
                "$lowerNamespace\\annotation\\example1\\dira\\innerservice" => array(
                    'class' => "$baseNamespace\\Annotation\\Example1\\DirA\\InnerService",
                ),
                'service.base' => array(
                    'class' => "$baseNamespace\\Annotation\\Example1\\Service",
                    'arguments' => array(
                        '@service.inner',
                    ),
                    'calls' => array(
                        array('setParameterA', array('%parameter.a%')),
                        array('setParameterB', array('%parameter.b%')),
                    ),
                    'properties' => array(
                        'propertyA' => '%parameter_of_property.a%',
                        'propertyB' => '%parameter_of_property.b%',
                    ),
                    'scope' => 'factory',
                    'alias' => array(
                        "$lowerNamespace\\annotation\\example1\\service",
                        "service.alias"
                    ),
                    'tags'        => array('service.tag'),
                    'preTriggers' => array(
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
            ))),

            // Example 2. basic factory methods and static factory methods
            array(__DIR__ . '/Stub/Annotation/Example2', array('services' => array(
                'service.factory' => array(
                    'class' => "$baseNamespace\\Annotation\\Example2\\Service",
                    'alias' => array(
                        "$lowerNamespace\\annotation\\example2\\service"
                    ),
                ),
                'service.inner' => array(
                    'factoryMethod' => array('@service.factory', 'createInnerService'),
                    'arguments'     => array(),
                ),
                'service.inner2' => array(
                    'factoryMethod' => array('@service.factory', 'createInnerService2'),
                    'arguments'     => array('%parameter.a%', '%parameter.b%'),
                ),
                'service.inner3' => array(
                    'factoryStaticMethod' => array("$baseNamespace\\Annotation\\Example2\\Service", 'createInnerService3'),
                    'arguments'           => array(),
                ),
                'service.inner4' => array(
                    'factoryStaticMethod' => array("$baseNamespace\\Annotation\\Example2\\Service", 'createInnerService4'),
                    'arguments'           => array('%parameter.a%', '%parameter.b%'),
                ),
            ))),

            /**
             * autowired configuration:
             *
             * @service
             * @autowired
             */

            // Example 1. not named service
            array(__DIR__ . '/Stub/Autowired/Example1', array('services' => array(
                "$lowerNamespace\\autowired\\example1\\service" => array(
                    'class' => "$baseNamespace\\Autowired\\Example1\\Service",
                ),
            ))),

            // Example 2. property for type
            array(__DIR__ . '/Stub/Autowired/Example2', array('services' => array(
                "$lowerNamespace\\autowired\\example2\\dira\\innerservice" => array(
                    'class' => "$baseNamespace\\Autowired\\Example2\\DirA\\InnerService",
                ),
                'service.base' => array(
                    'class' => "$baseNamespace\\Autowired\\Example2\\Service",
                    'alias' => array("$lowerNamespace\\autowired\\example2\\service"),
                    'properties' => array(
                        'inner' => "$lowerNamespace\\autowired\\example2\\dira\\innerservice",
                    ),
                ),
            ))),

            // Example 3. property for autowired value
            array(__DIR__ . '/Stub/Autowired/Example3', array('services' => array(
                'service.base' => array(
                    'class' => "$baseNamespace\\Autowired\\Example3\\Service",
                    'alias' => array("$lowerNamespace\\autowired\\example3\\service"),
                    'properties' => array(
                        'innerService'  => "service.inner",
                        'innerProperty' => "parameter.inner",
                    ),
                ),
            ))),

            // Example 4. methods for types
            array(__DIR__ . '/Stub/Autowired/Example4', array('services' => array(
                "$lowerNamespace\\autowired\\example4\\dira\\innerservice" => array(
                    'class' => "$baseNamespace\\Autowired\\Example4\\DirA\\InnerService",
                ),
                "$lowerNamespace\\autowired\\example4\\dira\\inner2service" => array(
                    'class' => "$baseNamespace\\Autowired\\Example4\\DirA\\Inner2Service",
                ),
                'service.base' => array(
                    'class' => "$baseNamespace\\Autowired\\Example4\\Service",
                    'alias' => array("$lowerNamespace\\autowired\\example4\\service"),
                    'calls' => array(
                        array('init', array(
                            "@$lowerNamespace\\autowired\\example4\\dira\\innerservice",
                            "@$lowerNamespace\\autowired\\example4\\dira\\inner2service",
                        )),
                    ),
                ),
            ))),

            // Example 5. methods for autowired annotation
            array(__DIR__ . '/Stub/Autowired/Example5', array('services' => array(
                "$lowerNamespace\\autowired\\example5\\dira\\innerservice" => array(
                    'class' => "$baseNamespace\\Autowired\\Example5\\DirA\\InnerService",
                ),
                "$lowerNamespace\\autowired\\example5\\dira\\inner2service" => array(
                    'class' => "$baseNamespace\\Autowired\\Example5\\DirA\\Inner2Service",
                ),
                'service.base' => array(
                    'class' => "$baseNamespace\\Autowired\\Example5\\Service",
                    'alias' => array("$lowerNamespace\\autowired\\example5\\service"),
                    'calls' => array(
                        array('init', array(
                            "@service.inner",
                            "@service.inner2",
                            "%parameter.input%",
                        )),
                    ),
                ),
            ))),

            // Example 6. methods for phpDoc types
            array(__DIR__ . '/Stub/Autowired/Example6', array('services' => array(
                "$lowerNamespace\\autowired\\example6\\dira\\innerservice" => array(
                    'class' => "$baseNamespace\\Autowired\\Example6\\DirA\\InnerService",
                ),
                "$lowerNamespace\\autowired\\example6\\dira\\inner2service" => array(
                    'class' => "$baseNamespace\\Autowired\\Example6\\DirA\\Inner2Service",
                ),
                'service.base' => array(
                    'class' => "$baseNamespace\\Autowired\\Example6\\Service",
                    'alias' => array("$lowerNamespace\\autowired\\example6\\service"),
                    'calls' => array(
                        array('init', array(
                            "@$lowerNamespace\\autowired\\example6\\dira\\innerservice",
                            "@$lowerNamespace\\autowired\\example6\\dira\\inner2service",
                        )),
                    ),
                ),
            ))),

            // Example 7. constructor
            array(__DIR__ . '/Stub/Autowired/Example7', array('services' => array(
                "$lowerNamespace\\autowired\\example7\\dira\\innerservice" => array(
                    'class' => "$baseNamespace\\Autowired\\Example7\\DirA\\InnerService",
                ),
                'service.base' => array(
                    'class' => "$baseNamespace\\Autowired\\Example7\\Service",
                    'alias' => array("$lowerNamespace\\autowired\\example7\\service"),
                    'arguments' => array(
                        '@service.inner',
                    ),
                ),
            ))),
        );
    }

    /**
     * @dataProvider getDataForTestExtractDiConfiguration
     *
     * @param string $dirPath
     * @param array $expectedConfig
     */
    public function testExtractDiConfiguration($dirPath, array $expectedConfig)
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

        $this->assertEquals($expectedConfig, $diConfig);
    }
}
