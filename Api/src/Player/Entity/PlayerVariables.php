<?php

declare(strict_types=1);

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\Collection\GameVariableCollection;
use Mush\Game\Entity\GameVariable;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Enum\PlayerVariableEnum;

#[ORM\Entity]
class PlayerVariables extends GameVariableCollection
{
    public function __construct(CharacterConfig $characterConfig)
    {
        $actionVariable = new GameVariable(
            $this,
            PlayerVariableEnum::ACTION_POINT,
            $characterConfig->getInitActionPoint(),
            $characterConfig->getMaxActionPoint(),
            0
        );

        $movementVariable = new GameVariable(
            $this,
            PlayerVariableEnum::MOVEMENT_POINT,
            $characterConfig->getInitMovementPoint(),
            $characterConfig->getMaxMovementPoint(),
            0
        );

        $moralVariable = new GameVariable(
            $this,
            PlayerVariableEnum::MORAL_POINT,
            $characterConfig->getInitMoralPoint(),
            $characterConfig->getMaxMoralPoint(),
            0
        );

        $healthVariable = new GameVariable(
            $this,
            PlayerVariableEnum::HEALTH_POINT,
            $characterConfig->getInitHealthPoint(),
            $characterConfig->getMaxHealthPoint(),
            0
        );

        $satietyVariable = new GameVariable(
            $this,
            PlayerVariableEnum::SATIETY,
            $characterConfig->getInitSatiety(),
            null,
            null
        );

        $sporeVariable = new GameVariable(
            $this,
            PlayerVariableEnum::SPORE,
            0,
            3,
            0
        );

        $privateChannelsVariable = new GameVariable(
            variableCollection: $this,
            name: PlayerVariableEnum::PRIVATE_CHANNELS,
            initValue: 0,
            maxValue: $characterConfig->getMaxNumberPrivateChannel(),
            minValue: 0
        );

        $triumphVariable = new GameVariable(
            variableCollection: $this,
            name: PlayerVariableEnum::TRIUMPH,
            initValue: 0,
            minValue: null,
        );

        parent::__construct([
            $actionVariable,
            $movementVariable,
            $moralVariable,
            $healthVariable,
            $satietyVariable,
            $sporeVariable,
            $privateChannelsVariable,
            $triumphVariable,
        ]);
    }
}
