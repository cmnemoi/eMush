<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\Collection\GameVariableCollection;
use Mush\Game\Entity\GameVariable;
use Mush\Project\Enum\ProjectVariablesEnum;

#[ORM\Entity]
class ProjectProgress extends GameVariableCollection
{
    public function __construct()
    {
        $projectProgress = new GameVariable(
            variableCollection: $this,
            name: ProjectVariablesEnum::PROGRESS->value,
            initValue: 0,
            maxValue: 100,
        );

        parent::__construct([$projectProgress]);
    }
}
