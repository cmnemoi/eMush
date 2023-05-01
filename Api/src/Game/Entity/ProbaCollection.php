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

            $cumuProba = $cumuProba + $probability;
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
            $cumuProba = $cumuProba + $probability;
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

    /**
     * Returns the highest probability in the collection.
     */
    public function max(): ?int
    {
        $data = $this->getProbabilities();
        if (empty($data)) {
            return null;
        }

        return max($data);
    }
}
