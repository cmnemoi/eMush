<?php

namespace Mush\Game\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * This entity stores a collection of string or int with an associated probability
 * It is used to perform a random selection with a weight.
 *
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

    public function withdrawElements(array $elements): ProbaCollection
    {
        if (count($elements) === 0) {
            return $this;
        }

        return new ProbaCollection(array_diff_key($this->toArray(), array_flip($elements)));
    }

    public function getTotalWeight(): int
    {
        $cumuProba = 0;
        foreach ($this as $element => $probability) {
            if (!is_int($probability)) {
                throw new \Exception('Probability weight should be provided as integers');
            }

            $cumuProba += $probability;
        }

        return $cumuProba;
    }

    /**
     * Returns an element from a random int between 0 and total Weight.
     */
    public function getElementFromDrawnProba(int $value): string|int
    {
        $cumuProba = 0;
        foreach ($this as $element => $probability) {
            $cumuProba += $probability;
            if ($cumuProba >= $value) {
                return $element;
            }
        }

        throw new \Exception("random value ($value) should be comprised between 0 and total Weight ($cumuProba)");
    }

    public function getProbabilities(): array
    {
        return $this->toArray();
    }

    /**
     * Returns the lowest element (index) in the collection.
     */
    public function minElement(): ?int
    {
        $probaCollectionAsArray = $this->getProbabilities();
        $keys = array_keys($probaCollectionAsArray);
        if (is_int($keys[0])) {
            return min($keys);
        }

        return null;
    }

    /**
     * Returns the highest element (index) in the collection.
     */
    public function maxElement(): ?int
    {
        $probaCollectionAsArray = $this->getProbabilities();
        $keys = array_keys($probaCollectionAsArray);
        if (is_int($keys[0])) {
            return max($keys);
        }

        return null;
    }
}
