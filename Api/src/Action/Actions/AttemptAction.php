<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AttemptAction extends AbstractAction
{
    protected RandomServiceInterface $randomService;

    public function __construct(
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventManager,
        ActionServiceInterface $actionService
    ) {
        $this->randomService = $randomService;
        parent::__construct(
                $eventManager,
                $actionService
            );
    }

    protected function makeAttempt(): ActionResult
    {
        $attempt = $this->actionService->getAttempt($this->player, $this->getActionName());

        $successChance = $this->actionService->getSuccessRate($this->action, $this->player, $this->getBaseRate());

        if ($this->randomService->isSuccessful($successChance)) {
            $this->player->removeStatus($attempt);
            $response = new Success();
        } else {
            $response = new Fail();
            $attempt->addCharge(1);
        }

        return $response;
    }

    public function getBaseRate(): int
    {
        return $this->action->getSuccessRate();
    }
}
