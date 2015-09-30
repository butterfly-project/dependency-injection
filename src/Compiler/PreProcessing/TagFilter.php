<?php

namespace Butterfly\Component\DI\Compiler\PreProcessing;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class TagFilter implements IFilter
{
    /**
     * @param array $configuration
     * @return array
     */
    public function filter(array $configuration)
    {
        $tags = array();

        foreach ($configuration as $serviceId => $serviceConfiguration) {
            if (isset($serviceConfiguration['tags'])) {
                $serviceTags = (array)$serviceConfiguration['tags'];
                foreach ($serviceTags as $tag) {
                    $tags[$tag][] = $serviceId;
                }
            }
        }

        $configuration['tags'] = $tags;

        return $configuration;
    }
}
