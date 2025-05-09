<?php

namespace Mush\Game\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\Entity\TriumphConfig;

/**
 * @template-extends ArrayCollection<int, TriumphConfig>
 */
class TriumphConfigCollection extends ArrayCollection
{
    public function getTriumph(string $name): ?TriumphConfig
    {
        $triumph = $this
            ->filter(static fn (TriumphConfig $triumphConfig) => $triumphConfig->getName() === $name)
            ->first();

        return $triumph === false ? null : $triumph;
    }

    public function getByNameOrThrow(string $name): TriumphConfig
    {
        $triumph = $this->getTriumph($name);

        if ($triumph === null) {
            throw new \RuntimeException("Triumph config {$name} not found");
        }

        return $triumph;
    }
}
