<?php

namespace Mush\Game\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\Entity\TitleConfig;

/**
 * @template-extends ArrayCollection<int, TitleConfig>
 */
class TitleConfigCollection extends ArrayCollection
{
    public function getTitle(string $name): ?TitleConfig
    {
        $title = $this
            ->filter(fn (TitleConfig $titleConfig) => $titleConfig->getName() === $name)
            ->first()
        ;

        return $title === false ? null : $title;
    }
}
