<?php

namespace Mush\Game\Entity;

use Mush\Game\Entity\Collection\GameVariableCollection;

interface GameVariableHolderInterface
{
    public function getVariableByName(string $variableName): GameVariable;

    public function getGameVariables(): GameVariableCollection;

    public function hasVariable(string $variableName): bool;
}
