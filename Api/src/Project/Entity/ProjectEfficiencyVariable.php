<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\Collection\GameVariableCollection;
use Mush\Game\Entity\GameVariable;

#[ORM\Entity]
class ProjectEfficiencyVariable extends GameVariableCollection
{
    public const string NAME = 'project_efficiency';

    public function __construct(int $baseEfficiency)
    {
        $projectEfficiency = new GameVariable(
            $this,
            name: self::NAME,
            initValue: $baseEfficiency,
            maxValue: 100,
            minValue: 0
        );

        parent::__construct([$projectEfficiency]);
    }
}
