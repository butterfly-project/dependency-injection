<?php

namespace Butterfly\Component\DI\Exception;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class IncorrectExpressionPathException extends \InvalidArgumentException
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $instance;

    /**
     * @param string $path
     * @param mixed $instance
     */
    public function __construct($path, $instance)
    {
        $this->path     = $path;
        $this->instance = $instance;

        parent::__construct(sprintf("Expression path '%s' not found in instance", $this->path));
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getInstance()
    {
        return $this->instance;
    }
}
