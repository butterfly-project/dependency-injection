<?php

namespace Syringe\Component\DI\Builder\ServiceVisitor;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class ConfigurationValidator implements IVisitor
{
    /**
     * @var array
     */
    protected $rightSections;

    /**
     * @var array
     */
    protected $rightScopes;

    /**
     * @param array $rightSections
     * @param array $rightScopes
     */
    public function __construct(array $rightSections, array $rightScopes)
    {
        $this->rightScopes   = $rightScopes;
        $this->rightSections = $rightSections;
    }

    /**
     * @return void
     */
    public function clean()
    {

    }

    /**
     * @param string $serviceId
     * @param array $configuration
     * @return void
     */
    public function visit($serviceId, array $configuration)
    {
        $errors = array();

        try {
            $this->checkRightSections($configuration);
        } catch (\InvalidArgumentException $e) {
            $errors[] = $e;
        }

        try {
            $this->checkInstanceConfiguration($configuration);
        } catch (\InvalidArgumentException $e) {
            $errors[] = $e;
        }

        try {
            $this->checkScopeConfiguration($configuration);
        } catch (\InvalidArgumentException $e) {
            $errors[] = $e;
        }

        if (!empty($errors)) {
            throw new InvalidConfigurationException(
                sprintf("Configuration of service '%s' is invalid", $serviceId),
                $errors
            );
        }
    }

    /**
     * @param array $configuration
     * @throws \InvalidArgumentException if sections is undefined
     */
    protected function checkRightSections(array $configuration)
    {
        $undefinedSections = array_diff(array_keys($configuration), $this->rightSections);

        if (!empty($undefinedSections)) {
            throw new \InvalidArgumentException(sprintf("Sections '%s' is undefined", implode(',', $undefinedSections)));
        }
    }

    /**
     * @param array $configuration
     * @throws \InvalidArgumentException if impossible to create a class
     */
    protected function checkInstanceConfiguration(array $configuration)
    {
        if (!isset($configuration['class']) &&
            !isset($configuration['factoryStaticMethod']) &&
            !isset($configuration['factoryMethod']) &&
            !isset($configuration['parent'])
        ) {
            throw new \InvalidArgumentException(
                "It is impossible to create a class. Add section 'class', 'factoryMethod' or 'factoryStaticMethod'"
            );
        }
    }

    /**
     * @param array $configuration
     * @throws \InvalidArgumentException if scope is invalid
     */
    protected function checkScopeConfiguration(array $configuration)
    {
        if (!empty($configuration['scope']) && !in_array($configuration['scope'], $this->rightScopes)) {
            throw new \InvalidArgumentException(sprintf("Scope '%s' is invalid", $configuration['scope']));
        }
    }
}
