<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
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
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->randomService = $randomService;
    }

    protected function checkResult(): ActionResult
    {
        $successChance = $this->getSuccessRate();

        // TODO: ADD ENHANCE EVENT
        if ($this->randomService->isSuccessful($successChance)) {
            return new Success();
        } else {
            return new Fail();
        }
    }

    public function getSuccessRate(): int
    {
        return $this->actionService->getSuccessRate($this->action, $this->player);
    }
}
