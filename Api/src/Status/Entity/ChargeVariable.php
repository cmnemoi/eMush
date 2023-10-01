<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\Collection\GameVariableCollection;
use Mush\Game\Entity\GameVariable;
use Mush\Status\Entity\Config\ChargeStatusConfig;

#[ORM\Entity]
class ChargeVariable extends GameVariableCollection
{
    public function __construct(ChargeStatusConfig $statusConfig)
    {
        $charge = new GameVariable(
            null,
            $statusConfig->getStatusName(),
            $statusConfig->getStartCharge(),
            $statusConfig->getMaxCharge(),
            0
        );

        parent::__construct([$charge]);
    }
}
