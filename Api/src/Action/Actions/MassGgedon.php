<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\HasStatus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class MassGgedon extends AbstractAction
{
    private const int SPORE_COST = 2;
    protected ActionEnum $name = ActionEnum::MASS_GGEDON;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private RoomLogServiceInterface $roomLogService,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new GameVariableLevel([
                'target' => GameVariableLevel::PLAYER,
                'checkMode' => GameVariableLevel::NOT_EQUALS,
                'variableName' => DaedalusVariableEnum::SPORE,
                'value' => self::SPORE_COST,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::HAS_USED_MASS_GGEDON,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::UNIQUE_ACTION,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->removeTwoSporesFromPlayer();
        $this->removeActionPointsToOtherPlayers();
        $this->makeAllPlayersDirty();
        $this->createDirtyLogForOtherPlayers();
        $this->createHasUsedMassGgedonStatus();
    }

    private function removeTwoSporesFromPlayer(): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            player: $this->player,
            variableName: PlayerVariableEnum::SPORE,
            quantity: -self::SPORE_COST,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function removeActionPointsToOtherPlayers(): void
    {
        $now = new \DateTime();
        foreach ($this->player->getDaedalus()->getAlivePlayers()->getAllExcept($this->player) as $player) {
            $playerVariableEvent = new PlayerVariableEvent(
                player: $player,
                variableName: PlayerVariableEnum::ACTION_POINT,
                quantity: -$this->getOutputQuantity(),
                tags: $this->getTags(),
                time: $now,
            );
            $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }

    private function makeAllPlayersDirty(): void
    {
        $now = new \DateTime();
        $tags = $this->getTags();
        $tags[] = ActionTypeEnum::ACTION_SUPER_DIRTY->value;
        foreach ($this->player->getDaedalus()->getAlivePlayers() as $player) {
            $this->statusService->createStatusFromName(
                statusName: PlayerStatusEnum::DIRTY,
                holder: $player,
                tags: $tags,
                time: $now,
            );
        }
    }

    private function createDirtyLogForOtherPlayers(): void
    {
        $now = new \DateTime();
        foreach ($this->player->getDaedalus()->getAlivePlayers()->getAllExcept($this->player) as $player) {
            $this->roomLogService->createLog(
                logKey: StatusEventLogEnum::SOILED_BY_MASS_GGEDON,
                place: $player->getPlace(),
                visibility: VisibilityEnum::PRIVATE,
                type: 'event_log',
                player: $player,
                dateTime: $now,
            );
        }
    }

    private function createHasUsedMassGgedonStatus(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HAS_USED_MASS_GGEDON,
            holder: $this->player,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }
}
