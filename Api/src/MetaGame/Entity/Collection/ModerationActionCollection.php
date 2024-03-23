<?php

namespace Mush\MetaGame\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\Player\Entity\Player;

/**
 * @template-extends ArrayCollection<int, Player>
 */
class ModerationActionCollection extends ArrayCollection
{
    public function getActiveSanction(): self
    {
        return $this->filter(fn (ModerationSanction $moderationAction) => $moderationAction->isSanctionActive());
    }

    public function isBanned(): bool
    {
        $activeBans = $this->filter(fn (ModerationSanction $moderationAction) => (
            $moderationAction->isSanctionActive()
            && $moderationAction->getModerationAction() === ModerationSanctionEnum::BAN_USER
        ));

        return $activeBans->count() > 0;
    }

    public function getWarnings(): self
    {
        return $this->filter(fn (ModerationSanction $moderationAction) => (
            $moderationAction->isSanctionActive()
            && $moderationAction->getModerationAction() === ModerationSanctionEnum::WARNING
        ));
    }
}
