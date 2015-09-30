<?php

namespace Butterfly\Component\DI\Compiler\PreProcessing;
use Butterfly\Component\Form\Transform\ITransformer;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class TagFilter implements ITransformer
{
    /**
     * @param mixed $value
     * @return mixed
     * @throws \InvalidArgumentException if incorrect value type
     */
    public function transform($value)
    {
        $tags = array();

        foreach ($value as $serviceId => $serviceConfiguration) {
            if (isset($serviceConfiguration['tags'])) {
                $serviceTags = (array)$serviceConfiguration['tags'];
                foreach ($serviceTags as $tag) {
                    $tags[$tag][] = $serviceId;
                }
            }
        }

        $value['tags'] = $tags;

        return $value;
    }
}
