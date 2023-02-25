<?php

namespace Mush\Game\Entity;

interface GameVariableHolderInterface
{
    public function getVariableByName(string $variableName): GameVariable;

    public function getGameVariables(): GameVariableCollection;

    public function hasVariable(string $variableName): bool;
}
