<?php

namespace Mush\Hunter\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;

class HunterService implements HunterServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;

    public function __construct(EntityManagerInterface $entityManager, RandomServiceInterface $randomService)
    {
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
    }

    public function makeHuntersShoot(HunterCollection $hunters): void
    {
    }

    public function putHuntersInPool(Daedalus $daedalus, int $nbHuntersToPutInPool): HunterCollection
    {
        $hunterPool = $daedalus->getHunterPool();
        $hunterName = $this->drawHunterNameToCreate($daedalus, $hunterPool);
        for ($i = 0; $i < $nbHuntersToPutInPool; ++$i) {
            $hunterPool->add($this->createHunterFromName($daedalus, $hunterName));
        }

        return $hunterPool;
    }

    public function unpoolHunters(Daedalus $daedalus, int $nbHuntersToUnpool): void
    {
        $hunterPool = $daedalus->getHunterPool();
        $nbMissingHunters = $nbHuntersToUnpool - $hunterPool->count();

        if ($nbMissingHunters > 0) {
            $hunterPool = $this->putHuntersInPool($daedalus, $nbMissingHunters);
        }

        $huntersToUnpool = $this->randomService->getRandomHuntersInPool($hunterPool, $nbHuntersToUnpool);
        $huntersToUnpool->map(fn ($hunter) => $this->unpoolHunter($hunter));
    }

    private function createHunterFromName(Daedalus $daedalus, string $hunterName): Hunter
    {
        /** @var HunterConfig $hunterConfig */
        $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getHunter($hunterName);
        if (!$hunterConfig) {
            throw new \Exception("Hunter config not found for hunter name $hunterName");
        }

        $hunter = new Hunter($hunterConfig, $daedalus);
        $daedalus->addHunter($hunter);

        $this->persistHunter($hunter);
        $this->persistDaedalus($daedalus);

        return $hunter;
    }

    private function drawHunterNameToCreate(Daedalus $daedalus, HunterCollection $hunterPool): string
    {
        $hunterTypes = $this->getHunterTypesToCreate($daedalus);

        foreach ($hunterTypes as $hunterType) {
            $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getHunter($hunterType);
            if (!$hunterConfig) {
                throw new \Exception("Hunter config not found for hunter name $hunterType");
            }

            if ($hunterPool->getAllHuntersByType($hunterType)->count() === $hunterConfig->getMaxPerWave()) {
                $hunterTypes->removeElement($hunterType);
            }
        }

        return current($this->randomService->getRandomElements($hunterTypes->toArray(), 1));
    }

    private function deleteHunter(Hunter $hunter): void
    {
        $this->entityManager->remove($hunter);
        $this->entityManager->flush();
    }

    private function getHunterTypesToCreate(Daedalus $daedalus): ArrayCollection
    {
        if ($daedalus->isInHardMode()) {
            return HunterEnum::getHardModeHunters();
        } elseif ($daedalus->isInVeryHardMode()) {
            return HunterEnum::getVeryHardModeHunters();
        }

        return HunterEnum::getNormalModeHunters();
    }

    private function persistDaedalus(Daedalus $daedalus): void
    {
        $this->entityManager->persist($daedalus);
        $this->entityManager->flush();
    }

    private function persistHunter(Hunter $hunter): void
    {
        $this->entityManager->persist($hunter);
        $this->entityManager->flush();
    }

    private function putHunterInPool(Hunter $hunter): void
    {
        $hunter->putInPool();
        $this->persistHunter($hunter);
    }

    private function unpoolHunter(Hunter $hunter): void
    {
        $hunter->unpool();
        $this->persistHunter($hunter);
    }
}
