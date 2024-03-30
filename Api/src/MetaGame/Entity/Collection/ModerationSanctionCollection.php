<?php

namespace Mush\MetaGame\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;
use Mush\Player\Entity\Player;

/**
 * @template-extends ArrayCollection<int, Player>
 */
class ModerationSanctionCollection extends ArrayCollection
{
    public function getActiveSanction(): self
    {
        return $this->filter(fn (ModerationSanction $moderationAction) => $moderationAction->getIsActive());
    }

    public function isBanned(): bool
    {
        $activeBans = $this->filter(fn (ModerationSanction $moderationAction) => (
            $moderationAction->getIsActive()
            && $moderationAction->getModerationAction() === ModerationSanctionEnum::BAN_USER
        ));

        return $activeBans->count() > 0;
    }

    public function getWarnings(): self
    {
        return $this->filter(fn (ModerationSanction $moderationAction) => (
            $moderationAction->getIsActive()
            && $moderationAction->getModerationAction() === ModerationSanctionEnum::WARNING
        ));
    }
}
