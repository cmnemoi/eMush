<?php

namespace Mush\Game\Entity;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\GameVariable;
use Mush\Modifier\Entity\ModifierHolder;

interface GameVariableHolderInterface
{
    public function getVariableByName(string $variableName): GameVariable;

    public function getGameVariables(): GameVariableCollection;
}
