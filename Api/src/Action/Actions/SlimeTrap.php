<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomIntegerServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SlimeTrap extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::SLIME_TRAP;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private GetRandomIntegerServiceInterface $getRandomInteger,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->createSlimeTrapStatusForTarget();
    }

    private function createSlimeTrapStatusForTarget(): void
    {
        /** @var ChargeStatus $slimeTrapStatus */
        $slimeTrapStatus = $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::SLIME_TRAP,
            holder: $this->playerTarget(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );

        $this->statusService->updateCharge(
            chargeStatus: $slimeTrapStatus,
            delta: $this->slimeTrapCharge(),
            tags: $this->getTags(),
            time: new \DateTime(),
            mode: VariableEventInterface::SET_VALUE,
        );
    }

    private function slimeTrapCharge(): int
    {
        return $this->getRandomInteger->execute(min: 1, max: $this->getOutputQuantity());
    }
}
