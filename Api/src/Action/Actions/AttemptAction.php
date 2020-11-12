<?php


namespace Mush\Action\Actions;


use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Game\Enum\StatusEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Status\Entity\Attempt;
use Mush\Status\Entity\Status;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AttemptAction extends Action
{
    protected StatusServiceInterface $statusService;
    protected RandomServiceInterface $randomService;
    protected SuccessRateServiceInterface $successRateService;

    public function __construct(
        RandomServiceInterface $randomService,
        SuccessRateServiceInterface $successRateService,
        EventDispatcherInterface $eventManager,
        StatusServiceInterface  $statusService
    ) {
        $this->randomService = $randomService;
        $this->successRateService = $successRateService;
        $this->statusService = $statusService;

        parent::__construct($eventManager);
    }

    private function getAttempt(): Attempt
    {
        /** @var Attempt $attempt */
        $attempt = $this->player
            ->getStatuses()
            ->filter(fn (Status $status) => StatusEnum::ATTEMPT === $status->getName())
            ->first()
        ;

        if ($attempt !== false && $attempt->getAction() !== $this->getActionName()) {
            // Remove other attempt status
            $this->player->removeStatus($attempt);
            $attempt = $this->statusService->createAttemptStatus(
                StatusEnum::ATTEMPT,
                $this->getActionName(),
                $this->player
            );
            $this->player->addStatus($attempt);
        } elseif ($attempt === false) { //Create Attempt
            $attempt = $this->statusService->createAttemptStatus(
                StatusEnum::ATTEMPT,
                $this->getActionName(),
                $this->player
            );
            $this->player->addStatus($attempt);
        }

        return $attempt;
    }

    protected function makeAttempt(int $baseRate, float $modificator): ActionResult
    {
        $attempt = $this->getAttempt();

        $successChance = $this->successRateService->getSuccessRate(
            $baseRate,
            $attempt->getCharge(),
            $modificator
        );

        $random = $this->randomService->randomPercent();

        if ($random <= $successChance) {
            $this->player->removeStatus($attempt);
            $response = new Success();
        } else {
            $response = new Fail();
            $attempt->addCharge(1);
        }

        return $response;
    }
}