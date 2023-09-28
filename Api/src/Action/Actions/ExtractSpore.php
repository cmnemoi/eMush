<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\HasStatus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ExtractSpore extends AbstractAction
{
    protected string $name = ActionEnum::EXTRACT_SPORE;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::MUSH, 'target' => HasStatus::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new GameVariableLevel([
            'target' => GameVariableLevel::DAEDALUS,
            'checkMode' => GameVariableLevel::IS_MIN,
            'variableName' => DaedalusVariableEnum::SPORE,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DAILY_SPORE_LIMIT,
        ]));
        $metadata->addConstraint(new GameVariableLevel([
            'target' => GameVariableLevel::PLAYER,
            'checkMode' => GameVariableLevel::IS_MAX,
            'variableName' => DaedalusVariableEnum::SPORE,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::PERSONAL_SPORE_LIMIT,
        ]));
    }

    protected function support(?LogParameterInterface $support, array $parameters): bool
    {
        return $support === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $player = $this->player;

        $playerModifierEvent = new PlayerVariableEvent(
            $player,
            PlayerVariableEnum::SPORE,
            1,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);

        $daedalusModifierEvent = new DaedalusVariableEvent(
            $player->getDaedalus(),
            DaedalusVariableEnum::SPORE,
            -1,
            $this->getAction()->getActionTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($daedalusModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
