<?php

namespace Butterfly\Component\DI\Tests\Stubs;

/**
 * @author Marat Fakhertdinov <marat.fakhertdinov@gmail.com>
 */
class UseTriggerService
{
    protected $triggerService;

    protected $preA;

    public function __construct(TriggerService $triggerService)
    {
        $this->preA           = $triggerService->getA();
        $this->triggerService = $triggerService;
    }

    public function getTriggerService()
    {
        return $this->triggerService;
    }

    public function getPreA()
    {
        return $this->preA;
    }
}
