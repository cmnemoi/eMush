<?php

namespace Mush\Hunter\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Entity\ProbaCollection;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Enum\HunterTargetEnum;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\AbstractHunterEvent;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Service\StatusService;

class HunterService implements HunterServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;
    private StatusService $statusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        StatusService $statusService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
    }

    public function changeVariable(string $variableName, Hunter $hunter, int $change, \DateTime $date, Player $author): void
    {
        $gameVariable = $hunter->getVariableByName($variableName);
        $newVariableValuePoint = $gameVariable->getValue() + $change;

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

    public function updateDaedalusHunterPoints(Daedalus $daedalus): void
    {
        $pointsToAdd = $daedalus->getDay() + 6;
        if ($daedalus->isInHardMode()) {
            ++$pointsToAdd;
        }
        if ($daedalus->isInVeryHardMode()) {
            $pointsToAdd += 2;
        }
        $pointsToAdd = intval($pointsToAdd * $this->getOverloadFactor($daedalus) + 0.5);

        $daedalus->addHunterPoints($pointsToAdd);
        $this->persistAndFlush([$daedalus]);
    }

    public function killHunter(Hunter $hunter): void
    {
        $daedalus = $hunter->getDaedalus();

        $daedalus->getAttackingHunters()->removeElement($hunter);
        $this->entityManager->remove($hunter);
        $this->persistAndFlush([$daedalus]);
    }

    public function makeHuntersShoot(HunterCollection $attackingHunters): void
    {
        $attackingHunters->map(fn (Hunter $hunter) => $this->makeHunterShoot($hunter));
    }

    public function unpoolHunters(Daedalus $daedalus, \DateTime $time): void
    {
        $hunterPoints = $daedalus->getHunterPoints();
        $hunterTypes = HunterEnum::getAll();
        $wave = new HunterCollection();
        while ($hunterPoints > 0) {
            $hunterNameToCreate = $this->randomService->getSingleRandomElementFromProbaArray(
                $this->getHunterProbaCollection($daedalus, $hunterTypes)->toArray()
            );
            if (!$hunterNameToCreate) {
                break;
            }
            
            $hunter = $this->createHunterFromName($daedalus, $hunterNameToCreate);

            $maxPerWave = $hunter->getHunterConfig()->getMaxPerWave();
            if ($maxPerWave && $wave->getAllHuntersByType($hunter->getName())->count() > $maxPerWave) {
                $hunterTypes->removeElement($hunterNameToCreate);
                continue;
            }

            $wave->add($hunter);

            $hunterPoints -= $hunter->getHunterConfig()->getDrawCost();
            $daedalus->setHunterPoints($hunterPoints);
        }

        $wave->map(fn ($hunter) => $this->unpoolHunter($hunter, $time));
        $this->persistAndFlush($wave->toArray());
        $this->persistAndFlush([$daedalus]);
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

        return $hunter;
    }

    private function createHunterStatuses(Hunter $hunter, \DateTime $time): void
    {
        $hunterConfig = $hunter->getHunterConfig();
        $statuses = $hunterConfig->getInitialStatuses();

        /** @var StatusConfig $statusConfig */
        foreach ($statuses as $statusConfig) {
            $this->statusService->createStatusFromConfig(
                $statusConfig,
                $hunter,
                [HunterPoolEvent::UNPOOL_HUNTERS],
                $time
            );
        }
    }

    private function getHunterProbaCollection(Daedalus $daedalus, ArrayCollection $hunterTypes): ProbaCollection
    {
        $difficultyMode = $daedalus->getDifficultyMode();
        $probaCollection = new ProbaCollection();

        foreach ($hunterTypes as $hunterType) {
            $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getHunter($hunterType);
            if (!$hunterConfig) {
                throw new \Exception("Hunter config not found for hunter name $hunterType");
            }

            if ($hunterConfig->getSpawnDifficulty() >= $difficultyMode) {
                continue;
            }

            $probaCollection->setElementProbability($hunterType, $hunterConfig->getDrawWeight());
        }

        return $probaCollection;
    }

    private function getOverloadFactor(Daedalus $daedalus): float
    {
        $threshold = 7 * $daedalus->getPlayers()->getPlayerAlive()->count();
        if ($daedalus->getDaedalusInfo()->getDailyActionPointsSpent() <= $threshold) {
            return 1;
        }

        return $daedalus->getDaedalusInfo()->getDailyActionPointsSpent() / $threshold;
    }

    private function makeHunterShoot(Hunter $hunter): void
    {
        if (!$hunter->canShoot()) {
            return;
        }

        $hunterDamage = $hunter->getHunterConfig()->getDamageRange();
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaArray($hunterDamage->toArray());
        if (!$damage) {
            return;
        }

        $successRate = $hunter->getHunterConfig()->getHitChance();
        if (!$this->randomService->isSuccessful($successRate)) {
            return;
        }

        // TODO: handle other targets
        switch ($hunter->getTarget()) {
            case HunterTargetEnum::DAEDALUS:
                $this->shootAtDaedalus($hunter, $damage);
                break;
            default:
                throw new \Exception("Unknown hunter target {$hunter->getTarget()}");
        }
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

    private function shootAtDaedalus(Hunter $hunter, int $damage): void
    {
        $daedalusVariableEvent = new DaedalusVariableEvent(
            $hunter->getDaedalus(),
            DaedalusVariableEnum::HULL,
            -$damage,
            [AbstractHunterEvent::MAKE_HUNTERS_SHOOT],
            new \DateTime()
        );

        $this->eventService->callEvent($daedalusVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function unpoolHunter(Hunter $hunter, \DateTime $time): void
    {
        $hunter->unpool();
        $this->createHunterStatuses($hunter, $time);
    }
}
