<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Status\Entity\Attempt;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AttemptAction extends AbstractAction
{
    protected StatusServiceInterface $statusService;
    protected RandomServiceInterface $randomService;
    protected SuccessRateServiceInterface $successRateService;

    private ?Attempt $attempt = null;

    public function __construct(
        RandomServiceInterface $randomService,
        SuccessRateServiceInterface $successRateService,
        EventDispatcherInterface $eventManager,
        StatusServiceInterface $statusService
    ) {
        $this->randomService = $randomService;
        $this->successRateService = $successRateService;
        $this->statusService = $statusService;

        parent::__construct($eventManager);
    }

    private function getAttempt(): Attempt
    {
        if ($this->attempt === null) {
            /** @var Attempt $attempt */
            $attempt = $this->player->getStatusByName(StatusEnum::ATTEMPT);

            if ($attempt && $attempt->getAction() !== $this->getActionName()) {
                // Re-initialize attempts with new action
                $attempt
                    ->setAction($this->getActionName())
                    ->setCharge(0)
                ;
            } elseif ($attempt === null) { //Create Attempt
                $attempt = $this->statusService->createAttemptStatus(
                    StatusEnum::ATTEMPT,
                    $this->getActionName(),
                    $this->player
                );
            }
            $this->attempt = $attempt;
        }

        return $this->attempt;
    }

    protected function makeAttempt(): ActionResult
    {
        $attempt = $this->getAttempt();

        $successChance = $this->getSuccessRate();

        if ($this->randomService->isSuccessful($successChance)) {
            $this->player->removeStatus($attempt);
            $response = new Success();
        } else {
            $response = new Fail();
            $attempt->addCharge(1);
        }

        return $response;
    }

    public function getSuccessRate(): int
    {
        $modificator = 1;

        $gears = $this->player->getApplicableGears(
            array_merge([$this->getActionName()], $this->action->getTypes()),
            [ReachEnum::INVENTORY],
            ModifierTargetEnum::PERCENTAGE
        );

        /** @var Gear $gear */
        foreach ($gears as $gear) {
            $modificator *= $gear->getModifier()->getDelta();
        }

        return $this->successRateService->getSuccessRate(
            $this->getBaseRate(),
            $this->getAttempt()->getCharge(),
            $modificator
        );
    }

    abstract protected function getBaseRate(): int;
}
