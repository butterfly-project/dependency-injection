<?php

namespace Syringe\Component\DI\Tests\Builder\ServiceVisitor;

use Syringe\Component\DI\Builder\ServiceVisitor\ConfigurationValidator;
use Syringe\Component\DI\Container;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ConfigurationValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurationValidator
     */
    protected $validator;

    protected function setUp()
    {
        $rightSections   = array(
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

        $rightScopes     = array(
            '',
            Container::SCOPE_SINGLETON,
            Container::SCOPE_FACTORY,
            Container::SCOPE_PROTOTYPE,
            Container::SCOPE_SYNTHETIC,
        );

        $this->validator = new ConfigurationValidator($rightSections, $rightScopes);
    }

    /**
     * @expectedException \Syringe\Component\DI\Builder\ServiceVisitor\InvalidConfigurationException
     */
    public function testErrorForUndefinedSection()
    {
        $this->validator->visit(1, array('undefined_section'));
    }

    /**
     * @expectedException \Syringe\Component\DI\Builder\ServiceVisitor\InvalidConfigurationException
     */
    public function testErrorIfImpossibleToCreateAClass()
    {
        $this->validator->visit(1, array('arguments' => array(1, 2, 3)));
    }

    /**
     * @expectedException \Syringe\Component\DI\Builder\ServiceVisitor\InvalidConfigurationException
     */
    public function testErrorIfUndefinedScope()
    {
        $this->validator->visit(1, array(
            'class' => 'ClassName',
            'scope' => 'undefined_scope',
        ));
    }
}
