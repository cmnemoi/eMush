<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\HasSkill;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class MixRationSpore extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::MIX_RATION_SPORE;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private StatusServiceInterface $statusService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new HasSkill([
                'skill' => SkillEnum::FUNGAL_KITCHEN,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new GameVariableLevel([
                'variableName' => PlayerVariableEnum::SPORE,
                'target' => GameVariableLevel::PLAYER,
                'checkMode' => GameVariableLevel::IS_MIN,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->contaminateFood();
        $this->removeOnePlayerSpore();
    }

    private function contaminateFood(): void
    {
        $this->statusService->createOrIncrementChargeStatus(
            name: EquipmentStatusEnum::CONTAMINATED,
            holder: $this->targetFood(),
            target: $this->player,
            tags: $this->getTags(),
        );
    }

    private function removeOnePlayerSpore(): void
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

    private function targetFood(): GameItem
    {
        return $this->target instanceof GameItem ? $this->target : throw new \RuntimeException('MixRationSpore action target should be a GameItem');
    }
}
