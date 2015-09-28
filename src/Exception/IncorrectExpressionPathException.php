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

        parent::__construct('Expression path not found in instance');
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
