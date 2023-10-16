<?php

namespace Mush\Action\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Game\Entity\Collection\GameVariableCollection;
use Mush\Game\Entity\GameVariable;
use Mush\Player\Enum\PlayerVariableEnum;

#[ORM\Entity]
class ActionVariables extends GameVariableCollection
{
    public function __construct()
    {
        $actionVariable = new GameVariable(
            $this,
            PlayerVariableEnum::ACTION_POINT,
            0,
            null,
            0
        );

        $movementVariable = new GameVariable(
            $this,
            PlayerVariableEnum::MOVEMENT_POINT,
            0,
            null,
            0
        );

        $moralVariable = new GameVariable(
            $this,
            PlayerVariableEnum::MORAL_POINT,
            0,
            null,
            0
        );

        $dirtinessVariable = new GameVariable(
            $this,
            ActionVariableEnum::PERCENTAGE_DIRTINESS,
            0,
            100,
            0
        );

        $injuryVariable = new GameVariable(
            $this,
            ActionVariableEnum::PERCENTAGE_INJURY,
            0,
            100,
            0
        );

        $successVariable = new GameVariable(
            $this,
            ActionVariableEnum::PERCENTAGE_SUCCESS,
            100,
            100,
            0
        );

        $criticalVariable = new GameVariable(
            $this,
            ActionVariableEnum::PERCENTAGE_CRITICAL,
            0,
            100,
            0
        );

        $outputVariable = new GameVariable(
            $this,
            ActionVariableEnum::OUTPUT_VARIABLE,
            0,
            null,
            0
        );

        parent::__construct([
            $actionVariable,
            $movementVariable,
            $moralVariable,
            $injuryVariable,
            $dirtinessVariable,
            $successVariable,
            $criticalVariable,
            $outputVariable,
        ]);
    }

    public function getVariablesAsArray(): array
    {
        if ($this->getVariableByName(ActionVariableEnum::PERCENTAGE_DIRTINESS)->getMinValue() === 100) {
            $arrayVariable = ['isSuperDirty' => true];
        } else {
            $arrayVariable = ['isSuperDirty' => false];
        }

        /** @var GameVariable $variable */
        foreach ($this->gameVariables as $variable) {
            $arrayVariable[$variable->getName()] = $variable->getValue();
        }

        return $arrayVariable;
    }

    public function updateVariablesFromArray(array $arrayVariables): void
    {
        foreach ($arrayVariables as $variableKey => $variable) {
            if ($variableKey !== 'isSuperDirty') {
                $this->setValueByName($variable, $variableKey);
            }
        }

        $gameVariable = $this->getVariableByName(ActionVariableEnum::PERCENTAGE_DIRTINESS);
        if ($arrayVariables['isSuperDirty']) {
            $gameVariable->setMinValue($gameVariable->getValue());
        } else {
            $gameVariable->setMinValue(0);
        }
    }
}
