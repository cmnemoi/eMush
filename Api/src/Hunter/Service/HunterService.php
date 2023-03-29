<?php

namespace Mush\Hunter\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Event\StatusEvent;

class HunterService implements HunterServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->randomService = $randomService;
    }

    public function makeHuntersShoot(HunterCollection $hunters): void
    {
    }

    public function putHuntersInPool(Daedalus $daedalus, int $nbHuntersToPutInPool): HunterCollection
    {
        $hunterPool = $daedalus->getHunterPool();
        for ($i = 0; $i < $nbHuntersToPutInPool; ++$i) {
            $hunterName = $this->drawHunterNameToCreate($daedalus, $hunterPool);
            $hunterPool->add($this->createHunterFromName($daedalus, $hunterName));
        }

        return $hunterPool;
    }

    public function unpoolHunters(Daedalus $daedalus, int $nbHuntersToUnpool): void
    {
        $hunterPool = $daedalus->getHunterPool();

        $nbHuntersToUnpool = min($nbHuntersToUnpool, $hunterPool->count());

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

        $this->entityManager->persist($hunter);
        $this->persistAndFlush($daedalus);

        $this->createHunterStatuses($hunter);

        $this->entityManager->persist($hunter);
        $this->persistAndFlush($daedalus);

        return $hunter;
    }

    private function createHunterStatuses(Hunter $hunter): void
    {
        $hunterConfig = $hunter->getHunterConfig();
        $statuses = $hunterConfig->getInitialStatuses();

        /** @var StatusConfig $statusConfig */
        foreach ($statuses as $statusConfig) {
            $statusAppliedEvent = new StatusEvent(
                $statusConfig->getStatusName(),
                $hunter,
                [HunterPoolEvent::UNPOOL_HUNTERS],
                new \DateTime()
            );
            $this->eventService->callEvent($statusAppliedEvent, StatusEvent::STATUS_APPLIED);
        }
    }

    private function drawHunterNameToCreate(Daedalus $daedalus, HunterCollection $hunterPool): string
    {
        $difficultyMode = $daedalus->getDifficultyMode();
        $hunterTypes = HunterEnum::getAll();

        foreach ($hunterTypes as $hunterType) {
            $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getHunter($hunterType);
            if (!$hunterConfig) {
                throw new \Exception("Hunter config not found for hunter name $hunterType");
            }

            if ($hunterConfig->getSpawnDifficulty() >= $difficultyMode) {
                $hunterTypes->removeElement($hunterType);
            }
            if ($hunterPool->getAllHuntersByType($hunterType)->count() === $hunterConfig->getMaxPerWave()) {
                $hunterTypes->removeElement($hunterType);
            }
        }

        return current($this->randomService->getRandomElements($hunterTypes->toArray(), 1));
    }

    private function persistAndFlush(object $object): void
    {
        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }

    private function putHunterInPool(Hunter $hunter): void
    {
        $hunter->putInPool();
        $this->persistAndFlush($hunter);
    }

    private function removeAndFlush(object $object): void
    {
        $this->entityManager->remove($object);
        $this->entityManager->flush();
    }

    private function unpoolHunter(Hunter $hunter): void
    {
        $hunter->unpool();
        $this->persistAndFlush($hunter);
    }
}
