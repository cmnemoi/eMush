<?php

namespace Mush\Hunter\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Player\Entity\Player;
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

    public function changeVariable(string $variableName, Hunter $hunter, int $change, \DateTime $date, Player $author): void
    {
        $gameVariable = $hunter->getVariableByName($variableName);

        $newVariableValuePoint = $gameVariable->getValue() + $change;
        $maxVariableValuePoint = $gameVariable->getMaxValue();
        $newVariableValuePoint = $this->getValueInInterval($newVariableValuePoint, 0, $maxVariableValuePoint);

        $hunter->setVariableValueByName($newVariableValuePoint, $variableName);

        switch ($variableName) {
            case HunterVariableEnum::HEALTH:
                if ($newVariableValuePoint === 0) {
                    $hunterDeathEvent = new HunterEvent(
                        $hunter,
                        VisibilityEnum::PUBLIC,
                        [HunterEvent::HUNTER_DEATH],
                        $date
                    );
                    $hunterDeathEvent->setAuthor($author);
                    $this->eventService->callEvent($hunterDeathEvent, HunterEvent::HUNTER_DEATH);
                }

                return;
        }

        $this->persistAndFlush([$hunter]);
    }

    public function makeHuntersShoot(HunterCollection $hunters): void
    {
    }

    public function killHunter(Hunter $hunter): void
    {
        $daedalus = $hunter->getDaedalus();
        $this->removeAndFlush([$hunter]);
        $this->persistAndFlush([$daedalus]);
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
        $hunter->setHunterVariables($hunterConfig);
        $daedalus->addHunter($hunter);

        $this->persistAndFlush([$hunter, $daedalus]);

        $this->createHunterStatuses($hunter);

        $this->persistAndFlush([$hunter, $daedalus]);

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

    private function getValueInInterval(int $value, ?int $min, ?int $max): int
    {
        if ($max !== null && $value > $max) {
            return $max;
        } elseif ($min !== null && $value < $min) {
            return $min;
        }

        return $value;
    }

    private function persistAndFlush(array $objects): void
    {
        foreach ($objects as $object) {
            $this->entityManager->persist($object);
        }
        $this->entityManager->flush();
    }

    private function putHunterInPool(Hunter $hunter): void
    {
        $hunter->putInPool();
        $this->persistAndFlush([$hunter]);
    }

    private function removeAndFlush(array $objects): void
    {
        foreach ($objects as $object) {
            $this->entityManager->remove($object);
        }
        $this->entityManager->flush();
    }

    private function unpoolHunter(Hunter $hunter): void
    {
        $hunter->unpool();
        $this->persistAndFlush([$hunter]);
    }
}
