<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\HasStatus;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TrapCloset extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::TRAP_CLOSET;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(
            new GameVariableLevel([
                'variableName' => PlayerVariableEnum::SPORE,
                'target' => GameVariableLevel::PLAYER,
                'checkMode' => GameVariableLevel::IS_MIN,
                'groups' => ['visibility'],
            ])
        );
        $metadata->addConstraint(
            new HasStatus([
                'status' => PlaceStatusEnum::MUSH_TRAPPED->value,
                'target' => HasStatus::PLAYER_ROOM,
                'contain' => false,
                'groups' => ['execute'],
                'message' => ActionImpossibleCauseEnum::BOOBY_TRAP_ALREADY_DONE,
            ])
        );
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
        $this->removeOneSporeToPlayer();
        $this->addTrappedStatusToTheRoom();
    }

    private function removeOneSporeToPlayer(): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            player: $this->player,
            variableName: PlayerVariableEnum::SPORE,
            quantity: -1,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function addTrappedStatusToTheRoom(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlaceStatusEnum::MUSH_TRAPPED->value,
            holder: $this->player->getPlace(),
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }
}