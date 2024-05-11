<?php

declare(strict_types=1);

namespace Mush\Action\Entity;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Player\Entity\Player;

interface ActionHolderInterface
{
    public function getClassName(): string;

    /**
     * This function retrieve the possible actions on this action holder.
     * Logic should be implemented in the class.
     */
    public function getActions(Player $activePlayer, ?ActionHolderEnum $actionTarget = null): Collection;
}
