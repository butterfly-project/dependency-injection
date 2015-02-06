<?php

namespace Butterfly\Component\DI\Compiler\ServiceCollector;

use Butterfly\Component\DI\Compiler\ServiceVisitor\IVisitor;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class TagCollector implements IVisitor, IConfigurationCollector
{
    /**
     * @var array
     */
    protected $tags = array();

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
    public function visit($serviceId, array $configuration)
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
