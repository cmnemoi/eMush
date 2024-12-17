<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\RemoveActionPointsFromPlayerServiceInterface;
use Mush\Player\Service\RemoveMovementPointsFromPlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Daunt extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::DAUNT;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private RemoveActionPointsFromPlayerServiceInterface $removeActionPointsFromPlayer,
        private RemoveMovementPointsFromPlayerServiceInterface $removeMovementPointsFromPlayer,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new Reach([
                'reach' => ReachEnum::ROOM,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::HAS_DAUNTED,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::DAILY_LIMIT,
            ]),
            new PreMush([
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::PRE_MUSH_RESTRICTED,
            ]),
        ]);
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
        $this->removeActionPointsFromTarget();
        $this->removeMovementPointsFromTarget();
        $this->createHasDauntedStatusForPlayer();
    }

    private function removeActionPointsFromTarget(): void
    {
        $this->removeActionPointsFromPlayer->execute(
            quantity: $this->actionPointsMalus(),
            player: $this->playerTarget(),
            tags: $this->getTags(),
        );
    }

    private function removeMovementPointsFromTarget(): void
    {
        $this->removeMovementPointsFromPlayer->execute(
            quantity: $this->movementPointsMalus(),
            player: $this->playerTarget(),
            tags: $this->getTags(),
        );
    }

    private function createHasDauntedStatusForPlayer(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_DAUNTED,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function actionPointsMalus(): int
    {
        return $this->getOutputQuantity();
    }

    private function movementPointsMalus(): int
    {
        return 2 * $this->getOutputQuantity();
    }
}
