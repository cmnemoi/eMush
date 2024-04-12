<?php

namespace Mush\Hunter\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @template-extends ArrayCollection<int, HunterConfig>
 */
class HunterConfigCollection extends ArrayCollection
{
    public function getHunter(string $name): ?HunterConfig
    {
        $hunter = $this
            ->filter(static fn (HunterConfig $hunterConfig) => $hunterConfig->getHunterName() === $name)
            ->first();

        return $hunter === false ? null : $hunter;
    }
}
