<?php

namespace Butterfly\Component\DI\Tests\Compiler\ServiceVisitor;

use Butterfly\Component\DI\Compiler\ServiceVisitor\ConfigurationValidator;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ConfigurationValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Butterfly\Component\DI\Compiler\ServiceVisitor\InvalidConfigurationException
     */
    public function testErrorForUndefinedSection()
    {
        $validator = $this->getValidator();

        $validator->visit(1, array('undefined_section'));
    }

    /**
     * @expectedException \Butterfly\Component\DI\Compiler\ServiceVisitor\InvalidConfigurationException
     */
    public function testErrorIfImpossibleToCreateAClass()
    {
        $validator = $this->getValidator();

        $validator->visit(1, array('arguments' => array(1, 2, 3)));
    }

    /**
     * @expectedException \Butterfly\Component\DI\Compiler\ServiceVisitor\InvalidConfigurationException
     */
    public function testErrorIfUndefinedScope()
    {
        $validator = $this->getValidator();

        $validator->visit(1, array(
            'class' => 'ClassName',
            'scope' => 'undefined_scope',
        ));
    }

    /**
     * @return ConfigurationValidator
     */
    protected function getValidator()
    {
        return new ConfigurationValidator();
    }
}
