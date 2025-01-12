<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Validator\GameVariableLevel;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\PlaceType;
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
    protected ActionEnum $name = ActionEnum::EXTRACT_SPORE;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::MUSH, 'target' => HasStatus::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new GameVariableLevel([
            'target' => GameVariableLevel::DAEDALUS,
            'checkMode' => GameVariableLevel::IS_MAX,
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
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
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
        $this->incrementPlayerSpores();
        $this->incrementDaedalusSpores();
    }

    private function incrementPlayerSpores(): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::SPORE,
            1,
            $this->getTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function incrementDaedalusSpores(): void
    {
        $daedalusModifierEvent = new DaedalusVariableEvent(
            $this->player->getDaedalus(),
            DaedalusVariableEnum::SPORE,
            1,
            $this->getTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($daedalusModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
