<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\Entity\Collection\GameVariableCollection;
use Mush\Game\Entity\GameVariable;

#[ORM\Entity]
class DaedalusVariables extends GameVariableCollection
{
    public function __construct(DaedalusConfig $daedalusConfig)
    {
        $fuelVariable = new GameVariable(
            $this,
            DaedalusVariableEnum::FUEL,
            $daedalusConfig->getInitFuel(),
            $daedalusConfig->getMaxFuel(),
            0
        );

        $oxygenVariable = new GameVariable(
            $this,
            DaedalusVariableEnum::OXYGEN,
            $daedalusConfig->getInitOxygen(),
            $daedalusConfig->getMaxOxygen(),
            0
        );

        $hullVariable = new GameVariable(
            $this,
            DaedalusVariableEnum::HULL,
            $daedalusConfig->getInitHull(),
            $daedalusConfig->getMaxHull(),
            0
        );

        $shieldVariable = new GameVariable(
            $this,
            DaedalusVariableEnum::SHIELD,
            $daedalusConfig->getInitShield(),
            $daedalusConfig->getMaxShield(),
            0
        );

        $sporeVariable = new GameVariable(
            $this,
            DaedalusVariableEnum::SPORE,
            initValue: 0,
            maxValue: $daedalusConfig->getDailySporeNb(),
        );

        $hunterPointsVariable = new GameVariable(
            $this,
            DaedalusVariableEnum::HUNTER_POINTS,
            $daedalusConfig->getInitHunterPoints(),
            minValue: 0
        );

        $combustionChamberVariable = new GameVariable(
            $this,
            DaedalusVariableEnum::COMBUSTION_CHAMBER_FUEL,
            $daedalusConfig->getInitCombustionChamberFuel(),
            $daedalusConfig->getMaxCombustionChamberFuel(),
            minValue: 0
        );

        parent::__construct([$fuelVariable, $oxygenVariable, $hullVariable, $shieldVariable, $sporeVariable, $hunterPointsVariable, $combustionChamberVariable]);
    }
}
