<?php

namespace Syringe\Component\DI;

use Syringe\Component\DI\ParameterBag\ParameterBag;

class ContainerConfigurationBuilder
{
    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {
        $this->addConfiguration($configuration);
    }

    /**
     * @param array $configuration
     * @return $this
     */
    public function addConfiguration(array $configuration)
    {
        $this->configuration = array_replace_recursive($this->configuration, $configuration);

        return $this;
    }

    /**
     * @return array
     */
    public function build()
    {
        $parameterBag = new ParameterBag($this->configuration);
        $parameterBag->resolve();

        $containerConfiguration = [
            'parameters' => [],
            'services'   => [],
            'tags'       => [],
            'aliases'    => [],
        ];

        $containerConfiguration['parameters'] = $parameterBag->all();

        $containerConfiguration['services'] = $containerConfiguration['parameters']['services'];
        unset($containerConfiguration['parameters']['services']);

        foreach ($containerConfiguration['services'] as $serviceId => $serviceConfiguration) {
            if (isset($serviceConfiguration['parent'])) {
                $parentConfiguration = $containerConfiguration['services'][$serviceConfiguration['parent']];
                unset($serviceConfiguration['parent']);
                $containerConfiguration['services'][$serviceId] = array_replace_recursive(
                    $parentConfiguration,
                    $serviceConfiguration
                );
            }

            if (isset($serviceConfiguration['tags'])) {
                $tags = (array)$serviceConfiguration['tags'];
                foreach ($tags as $tag) {
                    $containerConfiguration['tags'][$tag][] = '@' . $serviceId;
                }
                unset($containerConfiguration['services'][$serviceId]['tags']);
            }

            if (isset($serviceConfiguration['alias'])) {
                $aliases = (array)$serviceConfiguration['alias'];
                foreach ($aliases as $alias) {
                    $containerConfiguration['aliases'][$alias] = $serviceId;
                }
                unset($containerConfiguration['services'][$serviceId]['alias']);
            }
        }

        return $containerConfiguration;
    }
}
