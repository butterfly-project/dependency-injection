<?php

namespace Butterfly\Component\DI\Compiler\PreProcessing;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class TagFilter implements IFilter
{
    /**
     * @var array
     */
    protected $tags = array();

    /**
     * @param array $configuration
     * @return array
     */
    public function filter(array $configuration)
    {
        $this->clean();

        foreach ($configuration as $serviceId => $serviceConfiguration) {
            if (isset($serviceConfiguration['tags'])) {
                $tags = (array)$serviceConfiguration['tags'];
                foreach ($tags as $tag) {
                    $this->tags[$tag][] = $serviceId;
                }
            }
        }

        $configuration['tags'] = $this->tags;

        return $configuration;
    }

    /**
     * @return void
     */
    public function clean()
    {
        $this->tags = array();
    }

    /**
     * @param array $serviceId
     * @param array $configuration
     * @return void
     */
    public function visit($serviceId, $configuration)
    {
        if (isset($configuration['tags'])) {
            $tags = (array)$configuration['tags'];
            foreach ($tags as $tag) {
                $this->tags[strtolower($tag)][] = $serviceId;
            }
        }
    }

    /**
     * @return string
     */
    public function getSection()
    {
        return 'tags';
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->tags;
    }
}
