<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Event\EnhancePercentageRollEvent;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Enum\ActionOutputEnum;
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
        $threshold = $this->randomService->getSuccessThreshold();

        if ($successChance <= $threshold) {
            return new Success();
        }

        $event = new EnhancePercentageRollEvent(
            $this->player,
            $successChance,
            $threshold,
            true,
            $this->getActionName(),
            new \DateTime()
        );
        $event->addReason(ActionOutputEnum::SUCCESS);
        $this->eventService->callEvent($event, EnhancePercentageRollEvent::ACTION_ROLL_RATE);

        if ($event->getRate() <= $event->getThresholdRate()) {
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
