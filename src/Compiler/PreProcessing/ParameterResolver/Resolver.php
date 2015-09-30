<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Butterfly\Component\DI\Compiler\PreProcessing\ParameterResolver;

use Butterfly\Component\DI\Compiler\PreProcessing\IFilter;

/**
 * Source: Symfony 2 ParameterBag
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class Resolver implements IFilter
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param array $configuration
     * @return array
     */
    public function filter(array $configuration)
    {
        return $this->resolve($configuration);
    }

    /**
     * Replaces parameter placeholders (%name%) by their values for all parameters.
     *
     * @param array $parameters
     * @return array
     */
    public function resolve(array $parameters)
    {
        $this->parameters = $parameters;

        $resolvedParameters = array();
        foreach ($parameters as $key => $value) {
            try {
                $resolvedParameters[$key] = $this->unescapeValue($this->resolveValue($value));
            } catch (ParameterNotFoundException $e) {
                $e->setSourceKey($key);

                throw $e;
            }
        }

        return $resolvedParameters;
    }

    /**
     * @param string $name The parameter name
     * @return mixed  The parameter value
     * @throws ParameterNotFoundException if the parameter is not defined
     */
    protected function get($name)
    {
        if (!array_key_exists($name, $this->parameters)) {
            throw new ParameterNotFoundException($name);
        }

        return $this->parameters[$name];
    }

    /**
     * Replaces parameter placeholders (%name%) by their values.
     *
     * @param mixed $value     A value
     * @param array $resolving An array of keys that are being resolved (used internally to detect circular references)
     *
     * @return mixed The resolved value
     *
     * @throws ParameterNotFoundException if a placeholder references a parameter that does not exist
     * @throws ParameterCircularReferenceException if a circular reference if detected
     * @throws \RuntimeException when a given parameter has a type problem.
     */
    protected function resolveValue($value, array $resolving = array())
    {
        if (is_array($value)) {
            $args = array();
            foreach ($value as $k => $v) {
                $args[$this->resolveValue($k, $resolving)] = $this->resolveValue($v, $resolving);
            }

            return $args;
        }

        if (!is_string($value)) {
            return $value;
        }

        return $this->resolveString($value, $resolving);
    }

    /**
     * Resolves parameters inside a string
     *
     * @param string $value     The string to resolve
     * @param array  $resolving An array of keys that are being resolved (used internally to detect circular references)
     *
     * @return string The resolved string
     *
     * @throws ParameterNotFoundException if a placeholder references a parameter that does not exist
     * @throws ParameterCircularReferenceException if a circular reference if detected
     * @throws \RuntimeException when a given parameter has a type problem.
     */
    protected function resolveString($value, array $resolving = array())
    {
        // we do this to deal with non string values (Boolean, integer, ...)
        // as the preg_replace_callback throw an exception when trying
        // a non-string in a parameter value
        if (preg_match('/^%([^%\s]+)%$/', $value, $match)) {
            $key = $match[1];

            if (isset($resolving[$key])) {
                throw new ParameterCircularReferenceException(array_keys($resolving));
            }

            $resolving[$key] = true;

            return $this->resolveValue($this->get($key), $resolving);
        }

        $self = $this;

        return preg_replace_callback('/%%|%([^%\s]+)%/', function ($match) use ($self, $resolving, $value) {
            // skip %%
            if (!isset($match[1])) {
                return '%%';
            }

            $key = $match[1];
            if (isset($resolving[$key])) {
                throw new ParameterCircularReferenceException(array_keys($resolving));
            }

            $resolved = $self->get($key);

            if (!is_string($resolved) && !is_numeric($resolved)) {
                throw new \RuntimeException(sprintf('A string value must be composed of strings and/or numbers, but found parameter "%s" of type %s inside string value "%s".', $key, gettype($resolved), $value));
            }

            $resolved = (string) $resolved;
            $resolving[$key] = true;

            return $self->resolveString($resolved, $resolving);
        }, $value);
    }

    /**
     * @param mixed $value
     * @return array|mixed
     */
    protected function unescapeValue($value)
    {
        if (is_string($value)) {
            return str_replace('%%', '%', $value);
        }

        if (is_array($value)) {
            $result = array();
            foreach ($value as $k => $v) {
                $result[$k] = $this->unescapeValue($v);
            }

            return $result;
        }

        return $value;
    }
}
