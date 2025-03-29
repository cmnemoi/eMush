<?php

namespace Mush\Hunter\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Status\Enum\HunterStatusEnum;

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
        return $this->filter(static fn (Hunter $hunter) => ($hunter->getHunterConfig()->getHunterName() === $type));
    }

    /**
     * Returns a `HunterCollection` with all hunters except the specified type.
     */
    public function getAllExceptType(string $type): self
    {
        return $this->filter(static fn (Hunter $hunter) => ($hunter->getHunterConfig()->getHunterName() !== $type));
    }

    /**
     * Returns a `HunterCollection` with all hunters except the specified types.
     */
    public function getAllExceptTypes(array $types): self
    {
        return $this->filter(static fn (Hunter $hunter) => !\in_array($hunter->getHunterConfig()->getHunterName(), $types, true));
    }

    /**
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     */
    public function getAllHuntersSortedBy(string $criteriaName, bool $descending = false): self
    {
        $sortingMode = $descending ? Order::Descending : Order::Ascending;
        $criteria = Criteria::create()->orderBy([$criteriaName => $sortingMode]);

        // @var HunterCollection $result
        return $this->matching($criteria);
    }

    public function getHuntersAroundDaedalus(): self
    {
        return $this->filter(static fn (Hunter $hunter) => (!$hunter->isInPool()));
    }

    public function getHunterPool(): self
    {
        return $this->filter(static fn (Hunter $hunter) => $hunter->isInPool());
    }

    public function getOneHunterByType(string $type): ?Hunter
    {
        return $this->filter(static fn (Hunter $hunter) => ($hunter->getHunterConfig()->getHunterName() === $type))->first() ?: null;
    }

    /**
     * This method returns a ProbaCollection with the probability of each hunter to be drawn. Hunters are represented in Collection indexes by their id.
     */
    public function getProbaCollection(): ProbaCollection
    {
        $probaCollection = new ProbaCollection();
        $this->map(static fn (Hunter $hunter) => $probaCollection->setElementProbability($hunter->getId(), $hunter->getHunterConfig()->getDrawWeight()));

        return $probaCollection;
    }

    public function getAllExcept(Hunter $hunter): self
    {
        return $this->filter(static fn (Hunter $hunterToFilter) => $hunterToFilter->notEquals($hunter));
    }

    public function getAggroedTransports(): self
    {
        return $this->filter(static fn (Hunter $hunter) => $hunter->isTransport() && $hunter->hasStatus(HunterStatusEnum::AGGROED));
    }
}
