<?php

namespace Mush\Hunter\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Game\Entity\Collection\ProbaCollection;

/**
 * @template-extends ArrayCollection<int, Hunter>
 */
class HunterCollection extends ArrayCollection
{
    /**
     * `HunterCollection::getAllHuntersByType(HunterEnum::ASTEROID)` will return a HunterCollection with all the Asteroids in the collection.
     */
    public function getAllHuntersByType(string $type): self
    {
        return $this->filter(fn (Hunter $hunter) => ($hunter->getHunterConfig()->getHunterName() === $type));
    }

    /**
     * Returns a `HunterCollection` with all hunters except the specified type.
     */
    public function getAllHuntersExcept(string $type): self
    {
        return $this->filter(fn (Hunter $hunter) => ($hunter->getHunterConfig()->getHunterName() !== $type));
    }

    public function getAttackingHunters(): self
    {
        return $this->filter(fn (Hunter $hunter) => (!$hunter->isInPool()));
    }

    public function getHunterPool(): self
    {
        return $this->filter(fn (Hunter $hunter) => $hunter->isInPool());
    }

    /**
     * This method returns a ProbaCollection with the probability of each hunter to be drawn. Hunters are represented in Collection indexes by their id.
     */
    public function getProbaCollection(): ProbaCollection
    {
        $probaCollection = new ProbaCollection();
        $this->map(fn (Hunter $hunter) => $probaCollection->setElementProbability($hunter->getId(), $hunter->getHunterConfig()->getDrawWeight()));

        return $probaCollection;
    }
}
