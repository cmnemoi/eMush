<?php

namespace Mush\Hunter\Entity;

use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableCollection;
use Mush\Hunter\Enum\HunterVariableEnum;

class HunterVariables extends GameVariableCollection
{
    public function __construct(HunterConfig $hunterConfig)
    {
        $healthVariable = new GameVariable(
            variableCollection: $this,
            name: HunterVariableEnum::HEALTH,
            initValue: $hunterConfig->getInitialHealth(),
            minValue: 1
        );

        parent::__construct([$healthVariable]);
    }
}
