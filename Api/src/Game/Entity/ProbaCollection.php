<?php

namespace Mush\Game\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @template-extends ArrayCollection<string|int, int>
 */
class ProbaCollection extends ArrayCollection
{
    public function getElementProbability(string|int $key): ?int
    {
        return $this->get($key);
    }

    public function setElementProbability(string|int $key, int $value): static
    {
        $this->set($key, $value);

        return $this;
    }

    public function getProbabilities(): array
    {
        return $this->toArray();
    }

    /**
     * Returns the lowest probability in the collection.
     */
    public function min(): ?int
    {
        $data = $this->getProbabilities();
        if (empty($data)) {
            return null;
        }

        return min($data);
    }
}
