<?php

namespace Mush\Game\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @template-extends ArrayCollection<string|int, int>
 */
class ProbabilitiesCollection extends ArrayCollection
{
    public function getItemProbability(string|int $key): ?int
    {
        return $this->get($key);
    }

    public function setItemProbability(string|int $key, int $value): static
    {
        $this->set($key, $value);

        return $this;
    }

    public function getProbabilities(): array
    {
        return $this->toArray();
    }
}
