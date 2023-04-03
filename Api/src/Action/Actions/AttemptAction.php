<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\CriticalSuccess;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AttemptAction extends AbstractAction
{
    protected RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService
    ) {
        $this->randomService = $randomService;
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );
    }

    public function getCriticalSuccessRate(): int
    {
        return $this->actionService->getActionModifiedActionVariable(
            $this->player,
            $this->action,
            $this->parameter,
            ActionVariableEnum::PERCENTAGE_CRITICAL
        );
    }

    public function getSuccessRate(): int
    {
        return $this->actionService->getActionModifiedActionVariable(
            $this->player,
            $this->action,
            $this->parameter,
            ActionVariableEnum::PERCENTAGE_SUCCESS
        );
    }

    protected function checkResult(): ActionResult
    {
        $successChance = $this->getSuccessRate();

        if ($this->randomService->isSuccessful($successChance)) {
            return $this->drawCriticalSuccess();
        } else {
            return new Fail();
        }
    }

    private function drawCriticalSuccess(): ActionResult
    {
        if ($this->randomService->isSuccessful($this->getCriticalSuccessRate())) {
            return new CriticalSuccess();
        }

        return new Success();
    }
}
