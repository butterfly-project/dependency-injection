<?php

namespace Butterfly\Component\DI\Tests\Compiler\Annotation;

use Butterfly\Component\Annotations\ClassParser;
use Butterfly\Component\Annotations\Visitor\AnnotationsHandler;
use Butterfly\Component\DI\Compiler\Annotation\AnnotationConfigVisitor;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class AnnotationConfigVisitorTest extends \PHPUnit_Framework_TestCase
{
    public function getDataForTestExtractDiConfiguration()
    {
        $baseNamespace  = 'Butterfly\Component\DI\Tests\Compiler\Annotation\Stub\Annotation';
        $lowerNamespace = strtolower($baseNamespace);

        return array(

            // Example 1. not named service
            array(__DIR__ . '/Stub/Annotation/Example1', array('services' => array(
                "$lowerNamespace\\example1\\service" => array(
                    'class' => "$baseNamespace\\Example1\\Service",
                ),
            ))),

            // Example 3. property for type
            array(__DIR__ . '/Stub/Annotation/Example3', array('services' => array(
                "$lowerNamespace\\example3\\dira\\innerservice" => array(
                    'class' => "$baseNamespace\\Example3\\DirA\\InnerService",
                ),
                'service.base' => array(
                    'class' => "$baseNamespace\\Example3\\Service",
                    'alias' => array("$lowerNamespace\\example3\\service"),
                    'properties' => array(
                        'inner' => "$lowerNamespace\\example3\\dira\\innerservice",
                    ),
                ),
            ))),

            // Example 4. property for annotation
            array(__DIR__ . '/Stub/Annotation/Example4', array('services' => array(
                'service.base' => array(
                    'class' => "$baseNamespace\\Example4\\Service",
                    'alias' => array("$lowerNamespace\\example4\\service"),
                    'properties' => array(
                        'innerService'  => "service.inner",
                        'innerProperty' => "parameter.inner",
                    ),
                ),
            ))),

            // Example 5. methods for types
            array(__DIR__ . '/Stub/Annotation/Example5', array('services' => array(
                "$lowerNamespace\\example5\\dira\\innerservice" => array(
                    'class' => "$baseNamespace\\Example5\\DirA\\InnerService",
                ),
                "$lowerNamespace\\example5\\dira\\inner2service" => array(
                    'class' => "$baseNamespace\\Example5\\DirA\\Inner2Service",
                ),
                'service.base' => array(
                    'class' => "$baseNamespace\\Example5\\Service",
                    'alias' => array("$lowerNamespace\\example5\\service"),
                    'calls' => array(
                        array('init', array(
                            "@$lowerNamespace\\example5\\dira\\innerservice",
                            "@$lowerNamespace\\example5\\dira\\inner2service",
                        )),
                    ),
                ),
            ))),

            // Example 6. methods for phpDoc types
            array(__DIR__ . '/Stub/Annotation/Example6', array('services' => array(
                "$lowerNamespace\\example6\\dira\\innerservice" => array(
                    'class' => "$baseNamespace\\Example6\\DirA\\InnerService",
                ),
                "$lowerNamespace\\example6\\dira\\inner2service" => array(
                    'class' => "$baseNamespace\\Example6\\DirA\\Inner2Service",
                ),
                'service.base' => array(
                    'class' => "$baseNamespace\\Example6\\Service",
                    'alias' => array("$lowerNamespace\\example6\\service"),
                    'calls' => array(
                        array('init', array(
                            "@$lowerNamespace\\example6\\dira\\innerservice",
                            "@$lowerNamespace\\example6\\dira\\inner2service",
                        )),
                    ),
                ),
            ))),

            // Example 7. methods for autowired annotation
            array(__DIR__ . '/Stub/Annotation/Example7', array('services' => array(
                "$lowerNamespace\\example7\\dira\\innerservice" => array(
                    'class' => "$baseNamespace\\Example7\\DirA\\InnerService",
                ),
                "$lowerNamespace\\example7\\dira\\inner2service" => array(
                    'class' => "$baseNamespace\\Example7\\DirA\\Inner2Service",
                ),
                'service.base' => array(
                    'class' => "$baseNamespace\\Example7\\Service",
                    'alias' => array("$lowerNamespace\\example7\\service"),
                    'calls' => array(
                        array('init', array(
                            "@service.inner",
                            "@service.inner2",
                            "%parameter.input%",
                        )),
                    ),
                ),
            ))),

            // Example 10. constructor
            array(__DIR__ . '/Stub/Annotation/Example10', array('services' => array(
                "$lowerNamespace\\example10\\dira\\innerservice" => array(
                    'class' => "$baseNamespace\\Example10\\DirA\\InnerService",
                ),
                'service.base' => array(
                    'class' => "$baseNamespace\\Example10\\Service",
                    'alias' => array("$lowerNamespace\\example10\\service"),
                    'arguments' => array(
                        '@service.inner',
                    ),
                ),
            ))),

            // Example 11. annotation DI configuration
            array(__DIR__ . '/Stub/Annotation/Example11', array('services' => array(
                "$lowerNamespace\\example11\\dira\\innerservice" => array(
                    'class' => "$baseNamespace\\Example11\\DirA\\InnerService",
                ),
                'service.base' => array(
                    'class' => "$baseNamespace\\Example11\\Service",
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
                        "$lowerNamespace\\example11\\service",
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
        $annotations = ClassParser::createInstance()->parseClassesInDir($dirPath);
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
