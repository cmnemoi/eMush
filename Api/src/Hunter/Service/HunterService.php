<?php

namespace Mush\Hunter\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Enum\HunterTargetEnum;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Event\StrateguruWorkedEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusService;

final class HunterService implements HunterServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EventServiceInterface $eventService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private RandomServiceInterface $randomService,
        private StatusService $statusService,
    ) {}

    public function delete(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }
        $this->entityManager->flush();
    }

    public function findById(int $id): ?Hunter
    {
        return $this->entityManager->getRepository(Hunter::class)->find($id);
    }

    public function killHunter(Hunter $hunter, array $reasons, ?Player $author = null): void
    {
        $daedalus = $hunter->getDaedalus();

        $daedalus->getDaedalusInfo()->getClosedDaedalus()->incrementNumberOfHuntersKilled();

        $daedalus->getAttackingHunters()->removeElement($hunter);

        $this->delete([$hunter]);

        $hunterDeathEvent = new HunterEvent(
            $hunter,
            VisibilityEnum::PUBLIC,
            $reasons,
            new \DateTime()
        );
        $hunterDeathEvent->setAuthor($author);
        $this->eventService->callEvent($hunterDeathEvent, HunterEvent::HUNTER_DEATH);
    }

    public function makeHuntersShoot(HunterCollection $attackingHunters): void
    {
        /** @var Hunter $hunter */
        foreach ($attackingHunters as $hunter) {
            $numberOfActions = $hunter->getHunterConfig()->getNumberOfActionsPerCycle();
            for ($i = 0; $i < $numberOfActions; ++$i) {
                if (!$hunter->hasSelectedATarget()) {
                    $this->selectHunterTarget($hunter);

                    continue;
                }

                if (!$hunter->canShoot()) {
                    continue;
                }

                $successRate = $hunter->getHitChance();
                if (!$this->randomService->isSuccessful($successRate)) {
                    $this->addBonusToHunterHitChance($hunter);

                    continue;
                }

                if (!$hunter->getTarget()?->isInBattle()) {
                    continue;
                }

                $this->makeHunterShoot($hunter);

                // hunter must select a new target after a successful shot
                $hunter->resetTarget();

                // after a successful shot, reset hit chance to its default value
                $this->resetHunterHitChance($hunter);

                // destroy asteroid if it has shot
                if ($hunter->getName() === HunterEnum::ASTEROID) {
                    $this->killHunter($hunter, [HunterEvent::ASTEROID_DESTRUCTION]);
                }
            }
        }
    }

    public function persist(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }

    public function unpoolHunters(Daedalus $daedalus, array $tags, \DateTime $time): void
    {
        if (\in_array(DaedalusEvent::TRAVEL_FINISHED, $tags, true)) {
            $this->unpoolHuntersForCatchingWave($daedalus);
        } else {
            $this->unpoolHuntersForRandomWave($daedalus, $time);
        }
    }

    private function unpoolHuntersForRandomWave(Daedalus $daedalus, \DateTime $time): void
    {
        $hunterProbaCollection = $this->getHunterProbaCollection($daedalus, HunterEnum::getAll());

        /** @var ArrayCollection<array-key, string> $hunterTypes */
        $hunterTypes = new ArrayCollection($hunterProbaCollection->getKeys());
        $wave = new HunterCollection();

        while ($this->getHunterPoints($daedalus) >= $this->getMinCost($daedalus, $hunterTypes)) {
            $hunterProbaCollection = $this->getHunterProbaCollection($daedalus, $hunterTypes);

            $hunterNameToCreate = (string) $this->randomService->getSingleRandomElementFromProbaCollection($hunterProbaCollection);
            if (!$hunterNameToCreate) {
                break;
            }

            $hunter = $this->drawHunterFromPoolByName($daedalus, $hunterNameToCreate);
            if (!$hunter) {
                $hunter = $this->createHunterFromName($daedalus, $hunterNameToCreate);
            }

            // a hunter unpooled for a random wave should not have a target, so they don't shoot right away
            $hunter->resetTarget();

            // do not create a hunter if max per wave is reached
            $maxPerWave = $hunter->getHunterConfig()->getMaxPerWave();
            if ($maxPerWave && $wave->getAllHuntersByType($hunter->getName())->count() === $maxPerWave) {
                $hunterTypes->removeElement($hunterNameToCreate);
                $this->delete([$hunter]);

                continue;
            }

            $wave->add($hunter);
            $daedalus->removeHunterPoints($hunter->getHunterConfig()->getDrawCost());
        }

        $wave->map(fn ($hunter) => $this->createHunterStatuses($hunter, $time));
        $this->persist($wave->toArray());
        $this->persist([$daedalus]);
    }

    private function unpoolHuntersForCatchingWave(Daedalus $daedalus): void
    {
        $wave = new HunterCollection();
        for ($i = 0; $i < $this->getFollowingHunters($daedalus); ++$i) {
            $hunter = $this->drawHunterFromPoolByName($daedalus, HunterEnum::HUNTER);
            if (!$hunter) {
                $hunter = $this->createHunterFromName($daedalus, HunterEnum::HUNTER);
            }

            $wave->add($hunter);
            $daedalus->removeHunterPoints($hunter->getHunterConfig()->getDrawCost());
        }

        $this->persist($wave->toArray());
        $this->persist([$daedalus]);
    }

    private function addBonusToHunterHitChance(Hunter $hunter): void
    {
        $hunter->setHitChance($hunter->getHitChance() + $hunter->getHunterConfig()->getBonusAfterFailedShot());
        $this->persist([$hunter]);
    }

    private function getHunterPoints(Daedalus $daedalus): int
    {
        $hunterPoints = $daedalus->getHunterPoints();
        $strateguruWorked = $daedalus->getAlivePlayers()->hasPlayerWithSkill(SkillEnum::STRATEGURU) && $this->randomService->isSuccessful(50);

        if ($strateguruWorked) {
            $hunterPoints *= 0.66;
            $this->eventService->callEvent(new StrateguruWorkedEvent($daedalus), StrateguruWorkedEvent::class);
        }

        return (int) $hunterPoints;
    }

    private function getFollowingHunters(Daedalus $daedalus): int
    {
        $followingHuntersStatus = $daedalus->getChargeStatusByNameOrThrow(DaedalusStatusEnum::FOLLOWING_HUNTERS);

        $followingHunters = $followingHuntersStatus->getCharge();
        $strateguruWorked = $daedalus->getAlivePlayers()->hasPlayerWithSkill(SkillEnum::STRATEGURU) && $this->randomService->isSuccessful(50);

        if ($strateguruWorked) {
            $followingHunters *= 0.66;
            $this->eventService->callEvent(new StrateguruWorkedEvent($daedalus), StrateguruWorkedEvent::class);
        }

        return (int) $followingHunters;
    }

    private function createHunterFromName(Daedalus $daedalus, string $hunterName): Hunter
    {
        $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getByNameOrThrow($hunterName);

        $hunter = new Hunter($hunterConfig, $daedalus);
        $hunter->setHunterVariables($hunterConfig);
        $daedalus->addHunter($hunter);

        $this->persist([$hunter, $daedalus]);

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

    private function drawHunterFromPoolByName(Daedalus $daedalus, string $hunterName): ?Hunter
    {
        $hunterPool = $daedalus->getHunterPool()->getAllHuntersByType($hunterName);

        if ($hunterPool->isEmpty()) {
            return null;
        }

        $draw = $this->randomService->getRandomElements($hunterPool->toArray(), number: 1);
        $hunter = reset($draw);

        $hunter->unpool();

        return $hunter;
    }

    /** @param ArrayCollection<array-key, string> $hunterTypes */
    private function getHunterProbaCollection(Daedalus $daedalus, ArrayCollection $hunterTypes): ProbaCollection
    {
        $currentDifficulty = $daedalus->getDifficultyMode();
        $probaCollection = new ProbaCollection();

        foreach ($hunterTypes as $hunterType) {
            $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getHunter($hunterType);
            if (!$hunterConfig) {
                continue;
            }

            $hunterAvailableInCurrentDifficulty = $hunterConfig->getSpawnDifficulty() <= $currentDifficulty;
            if ($hunterAvailableInCurrentDifficulty) {
                $probaCollection->setElementProbability($hunterType, $hunterConfig->getDrawWeight());
            }
        }

        return $probaCollection;
    }

    /** @param ArrayCollection<array-key, string> $hunterTypes */
    private function getMinCost(Daedalus $daedalus, ArrayCollection $hunterTypes): int
    {
        $minCost = 0;
        foreach ($hunterTypes as $hunterType) {
            $hunterConfig = $daedalus->getGameConfig()->getHunterConfigs()->getByNameOrThrow($hunterType);

            if ($minCost === 0 || $minCost > $hunterConfig->getDrawCost()) {
                $minCost = $hunterConfig->getDrawCost();
            }
        }

        return $minCost;
    }

    private function getHunterDamage(Hunter $hunter): int
    {
        if ($hunter->getName() === HunterEnum::ASTEROID) {
            return $hunter->getHealth();
        }

        $hunterDamageRange = $hunter->getHunterConfig()->getDamageRange();

        return (int) $this->randomService->getSingleRandomElementFromProbaCollection($hunterDamageRange);
    }

    /**
     * @psalm-suppress NoValue
     */
    private function makeHunterShoot(Hunter $hunter): void
    {
        $hunterTarget = $hunter->getTargetEntityOrThrow();

        // @TODO: handle hunter and merchant targets in the future
        switch ($hunterTarget) {
            case $hunterTarget instanceof Daedalus:
                $this->shootAtDaedalus($hunter);

                break;

            case $hunterTarget instanceof GameEquipment:
                $this->shootAtPatrolShip($hunter);

                break;

            case $hunterTarget instanceof Player:
                $this->shootAtPlayer($hunter);

                break;

            default:
                throw new \Exception("Unknown hunter target {$hunter->getTarget()?->getType()}");
        }
    }

    private function resetHunterHitChance(Hunter $hunter): void
    {
        $hunter->setHitChance($hunter->getHunterConfig()->getHitChance());
        $this->persist([$hunter]);
    }

    private function selectHunterTarget(Hunter $hunter): void
    {
        // by default, aim at Daedalus
        $selectedTarget = new HunterTarget($hunter);
        $hunter->setTarget($selectedTarget);

        $targetProbabilities = $hunter->getHunterConfig()->getTargetProbabilities();

        // First, try to aim at one patrol ship in battle
        $patrolShips = EquipmentEnum::getPatrolShips()
            ->map(fn (string $patrolShip) => $this->gameEquipmentService->findEquipmentByNameAndDaedalus($patrolShip, $hunter->getDaedalus())->first())
            ->filter(static fn ($patrolShip) => $patrolShip instanceof GameEquipment);
        $patrolShipsInBattle = $patrolShips->filter(static fn (GameEquipment $patrolShip) => $patrolShip->isInSpaceBattle());

        if (!$patrolShipsInBattle->isEmpty()) {
            $successRate = $targetProbabilities->getElementProbability(HunterTargetEnum::PATROL_SHIP);
            if ($this->randomService->isSuccessful($successRate)) {
                $patrolShip = $this->randomService->getRandomElement($patrolShipsInBattle->toArray());
                $selectedTarget->setTargetEntity($patrolShip);

                return;
            }
        }

        // If we fail to aim at a patrol ship, try to aim at a player in battle
        $playersInBattle = $hunter->getDaedalus()->getAlivePlayersInSpaceBattle();
        if (!$playersInBattle->isEmpty()) {
            $successRate = $targetProbabilities->getElementProbability(HunterTargetEnum::PLAYER);
            if ($this->randomService->isSuccessful($successRate)) {
                $player = $this->randomService->getRandomElement($playersInBattle->toArray());
                $selectedTarget->setTargetEntity($player);
            }
        }
    }

    private function shootAtDaedalus(Hunter $hunter): void
    {
        /** @var Daedalus $daedalus */
        $daedalus = $hunter->getTargetEntityOrThrow();

        $shouldHurtShield = $daedalus->isPlasmaShieldActive() && $hunter->isNotAnAsteroid();
        $damage = $this->getHunterDamage($hunter);

        // Get the lowest value between the shield or the damage given to it if this one is lower.
        $damageOnShield = $shouldHurtShield ? min($damage, $daedalus->getShield()) : 0;
        $damageOnHull = $damage - $damageOnShield;

        // shoot at shield first
        $daedalusVariableEvent = new DaedalusVariableEvent(
            daedalus: $daedalus,
            variableName: DaedalusVariableEnum::SHIELD,
            quantity: -$damageOnShield,
            tags: [HunterEvent::HUNTER_SHOT],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusVariableEvent, VariableEventInterface::CHANGE_VARIABLE);

        // then shoot at hull if there is any damage left to deal
        if ($damageOnHull === 0) {
            return;
        }
        $daedalusVariableEvent = new DaedalusVariableEvent(
            daedalus: $daedalus,
            variableName: DaedalusVariableEnum::HULL,
            quantity: -$damageOnHull,
            tags: [HunterEvent::HUNTER_SHOT],
            time: new \DateTime()
        );
        $this->eventService->callEvent($daedalusVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function shootAtPatrolShip(Hunter $hunter): void
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $hunter->getTargetEntityOrThrow();

        /** @var ChargeStatus $patrolShipArmor */
        $patrolShipArmor = $patrolShip->getStatusByNameOrThrow(EquipmentStatusEnum::PATROL_SHIP_ARMOR);

        // temporary reset the target in case patrolShip is destroyed
        /** @var HunterTarget $patrolShipTarget */
        $patrolShipTarget = $hunter->getTarget();
        $hunter->resetTarget();

        $chargeStatus = $this->statusService->updateCharge(
            chargeStatus: $patrolShipArmor,
            delta: -$this->getHunterDamage($hunter),
            tags: [HunterEvent::HUNTER_SHOT],
            time: new \DateTime()
        );

        // if patrol ship is not destroyed, put it back as hunter target
        if ($chargeStatus?->getVariableByName($chargeStatus->getName())->isMin() === false) {
            $hunter->setTarget($patrolShipTarget);
        }

        $this->persist([$hunter]);
    }

    private function shootAtPlayer(Hunter $hunter): void
    {
        /** @var Player $player */
        $player = $hunter->getTargetEntityOrThrow();

        $playerVariableEvent = new PlayerVariableEvent(
            player: $player,
            variableName: PlayerVariableEnum::HEALTH_POINT,
            quantity: -$this->getHunterDamage($hunter),
            tags: [HunterEvent::HUNTER_SHOT],
            time: new \DateTime()
        );

        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
