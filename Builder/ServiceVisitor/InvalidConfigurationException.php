<?php

namespace Syringe\Component\DI\Builder\ServiceVisitor;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class InvalidConfigurationException extends \InvalidArgumentException
{
    /**
     * @var \InvalidArgumentException[]
     */
    protected $errors;

    /**
     * @param string $message
     * @param \InvalidArgumentException[] $errors
     */
    public function __construct($message, array $errors = array())
    {
        parent::__construct($message);

        foreach ($errors as $error) {
            $this->addError($error);
        }
    }

    /**
     * @param \InvalidArgumentException $error
     */
    protected function addError(\InvalidArgumentException $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @return \InvalidArgumentException[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
