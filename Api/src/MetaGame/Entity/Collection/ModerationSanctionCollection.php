<?php

namespace Mush\MetaGame\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\MetaGame\Entity\ModerationSanction;
use Mush\MetaGame\Enum\ModerationSanctionEnum;

/**
 * @template-extends ArrayCollection<int, ModerationSanction>
 */
class ModerationSanctionCollection extends ArrayCollection
{
    /**
     * @psalm-suppress PossiblyFalseReference
     * If `$bans` is not empty, we know for sure `$bans->first()` is not false, come on, psalm...
     */
    public function isBanned(): bool
    {
        $bans = $this->filter(
            static fn (ModerationSanction $moderationAction) => $moderationAction->isType(ModerationSanctionEnum::BAN_USER)
        );

        $activeBan = $bans->first();
        if ($bans->isEmpty()) {
            return false;
        }

        // get the last ban given
        /** @var ModerationSanction $ban */
        foreach ($bans as $ban) {
            if ($ban->getStartDate() > $activeBan->getStartDate()) {
                $activeBan = $ban;
            }
        }

        return $activeBan ? $activeBan->isActive() : false;
    }
}
