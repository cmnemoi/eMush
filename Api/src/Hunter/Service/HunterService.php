<?php

namespace Mush\Hunter\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\ProbaCollection;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Enum\HunterTargetEnum;
use Mush\Hunter\Event\AbstractHunterEvent;
use Mush\Hunter\Event\HunterEvent;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Mush\Status\Service\StatusService;
use Psr\Log\LoggerInterface;

class HunterService implements HunterServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private LoggerInterface $logger;
    private RandomServiceInterface $randomService;
    private StatusService $statusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        GameEquipmentServiceInterface $gameEquipmentService,
        LoggerInterface $logger,
        RandomServiceInterface $randomService,
        StatusService $statusService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->logger = $logger;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
    }

    public function findById(int $id): ?Hunter
    {
        return $this->entityManager->getRepository(Hunter::class)->find($id);
    }

    public function killHunter(Hunter $hunter, array $reasons, Player $author = null): void
    {
        $daedalus = $hunter->getDaedalus();

        $daedalus->getDaedalusInfo()->getClosedDaedalus()->incrementNumberOfHuntersKilled();

        $daedalus->getAttackingHunters()->removeElement($hunter);

        $this->entityManager->remove($hunter);
        $this->persist([$daedalus]);

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

    public function unpoolHunters(Daedalus $daedalus, \DateTime $time): void
    {
        $hunterPoints = $daedalus->getHunterPoints();
        $hunterTypes = HunterEnum::getAll();
        $wave = new HunterCollection();

        while ($hunterPoints > 0) {
            $hunterProbaCollection = $this->getHunterProbaCollection($daedalus, $hunterTypes);

            // do not create a hunter if not enough points
            if ($hunterPoints < $hunterProbaCollection->minElement()) {
                break;
            }
            $hunterNameToCreate = $this->randomService->getSingleRandomElementFromProbaCollection(
                $hunterProbaCollection
            );
            if (!is_string($hunterNameToCreate)) {
                break;
            }

            $hunter = $this->createHunterFromName($daedalus, $hunterNameToCreate);

            // do not create a hunter if max per wave is reached
            $maxPerWave = $hunter->getHunterConfig()->getMaxPerWave();
            if ($maxPerWave && $wave->getAllHuntersByType($hunter->getName())->count() === $maxPerWave) {
                $hunterTypes->removeElement($hunterNameToCreate);
                $this->delete([$hunter]);
                continue;
            }

            $wave->add($hunter);

            $hunterPoints -= $hunter->getHunterConfig()->getDrawCost();
            $daedalus->setHunterPoints($hunterPoints);
        }

        $wave->map(fn ($hunter) => $this->createHunterStatuses($hunter, $time));
        $this->persist($wave->toArray());
        $this->persist([$daedalus]);
    }

    private function addBonusToHunterHitChance(Hunter $hunter): void
    {
        $hunter->setHitChance($hunter->getHitChance() + $hunter->getHunterConfig()->getBonusAfterFailedShot());
        $this->persist([$hunter]);
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

    private function delete(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }
        $this->entityManager->flush();
    }

    private function dropScrap(Hunter $hunter): void
    {
        $scrapDropTable = $hunter->getHunterConfig()->getScrapDropTable();
        $numberOfDroppedScrap = $hunter->getHunterConfig()->getNumberOfDroppedScrap();

        $numberOfScrapToDrop = (int) $this->randomService->getSingleRandomElementFromProbaCollection($numberOfDroppedScrap);
        $scrapToDrop = $this->randomService->getRandomElementsFromProbaCollection($scrapDropTable, $numberOfScrapToDrop);

        foreach ($scrapToDrop as $scrap) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $scrap,
                equipmentHolder: $hunter->getSpace(),
                reasons: [HunterEvent::HUNTER_DEATH],
                time: new \DateTime(),
                visibility: VisibilityEnum::HIDDEN
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
                $this->logger->error("Hunter config not found for hunter name $hunterType", [
                    'daedalus' => $daedalus->getId(),
                ]);
                continue;
            }

            if ($hunterConfig->getSpawnDifficulty() > $difficultyMode) {
                continue;
            }

            $probaCollection->setElementProbability($hunterType, $hunterConfig->getDrawWeight());
        }

        return $probaCollection;
    }

    private function getHunterDamage(Hunter $hunter): ?int
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
        $damage = $this->getHunterDamage($hunter);
        if (!$damage) {
            return;
        }

        $hunterTarget = $hunter->getTarget()?->getTargetEntity();

        // @TODO: handle hunter and merchant targets in the future
        switch ($hunterTarget) {
            case $hunterTarget instanceof Daedalus:
                $this->shootAtDaedalus($hunterTarget, $damage);
                break;
            case $hunterTarget instanceof GameEquipment:
                $this->shootAtPatrolShip($hunterTarget, $damage, $hunter);
                break;
            case $hunterTarget instanceof Player:
                $this->shootAtPlayer($hunterTarget, $damage);
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

        // if there is no patrol ship in battle, remove patrol ship target from probabilities to draw
        $patrolShips = EquipmentEnum::getPatrolShips()
            ->map(fn (string $patrolShip) => $this->gameEquipmentService->findByNameAndDaedalus($patrolShip, $hunter->getDaedalus())->first())
            ->filter(fn ($patrolShip) => $patrolShip instanceof GameEquipment)
        ;
        $patrolShipsInBattle = $patrolShips->filter(fn (GameEquipment $patrolShip) => $patrolShip->isInSpaceBattle());

        if (!$patrolShipsInBattle->isEmpty()) {
            $successRate = $targetProbabilities?->get(HunterTargetEnum::PATROL_SHIP);
            if ($successRate === null) {
                throw new \LogicException('Patrol ship target probability should not be null');
            }
            if ($this->randomService->isSuccessful($successRate)) {
                $draw = $this->randomService->getRandomElements($patrolShipsInBattle->toArray(), number: 1);
                $patrolShip = reset($draw);
                $selectedTarget->setTargetEntity($patrolShip);

                return;
            }
        }

        $playersInBattle = $hunter->getDaedalus()->getPlayers()->getPlayerAlive()->filter(fn (Player $player) => $player->isInSpaceBattle());
        if (!$playersInBattle->isEmpty()) {
            $successRate = $targetProbabilities?->get(HunterTargetEnum::PLAYER);
            if ($successRate === null) {
                throw new \LogicException('Player target probability should not be null');
            }
            if ($this->randomService->isSuccessful($successRate)) {
                $draw = $this->randomService->getRandomElements($playersInBattle->toArray(), number: 1);
                $player = reset($draw);
                $selectedTarget->setTargetEntity($player);
            }
        }
    }

    private function shootAtDaedalus(Daedalus $daedalus, int $damage): void
    {
        $daedalusVariableEvent = new DaedalusVariableEvent(
            daedalus: $daedalus,
            variableName: DaedalusVariableEnum::HULL,
            quantity: -$damage,
            tags: [AbstractHunterEvent::HUNTER_SHOT],
            time: new \DateTime()
        );

        $this->eventService->callEvent($daedalusVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function shootAtPatrolShip(GameEquipment $patrolShip, int $damage, Hunter $hunter): void
    {
        /** @var ?ChargeStatus $patrolShipArmor */
        $patrolShipArmor = $patrolShip->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        if (!$patrolShipArmor) {
            throw new \LogicException("Patrol ship {$patrolShip->getName()} should have a patrol ship armor status");
        }

        /** @var ?Player|false $patrolShipPilot */
        $patrolShipPilot = $patrolShip->getDaedalus()->getPlaceByName($patrolShip->getName())?->getPlayers()->getPlayerAlive()->first();
        if (!$patrolShipPilot instanceof Player) {
            throw new \LogicException("Patrol ship {$patrolShip->getName()} should have a pilot");
        }

        $this->statusService->updateCharge(
            chargeStatus: $patrolShipArmor,
            delta: -$damage,
            tags: [AbstractHunterEvent::HUNTER_SHOT],
            time: new \DateTime()
        );

        if ($patrolShipArmor->getCharge() <= 0) {
            // reset hunter target so the patrol ship can be safely deleted
            $hunter->setTarget(new HunterTarget($hunter));
            $this->persist([$hunter]);

            $statusEvent = new StatusEvent(
                statusName: EquipmentStatusEnum::PATROL_SHIP_ARMOR,
                holder: $patrolShip,
                tags: [AbstractHunterEvent::HUNTER_SHOT],
                time: new \DateTime()
            );
            $statusEvent->setAuthor($patrolShipPilot);

            $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_CHARGE_UPDATED);
        }
    }

    private function shootAtPlayer(Player $player, int $damage): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            player: $player,
            variableName: PlayerVariableEnum::HEALTH_POINT,
            quantity: -$damage,
            tags: [AbstractHunterEvent::HUNTER_SHOT],
            time: new \DateTime()
        );

        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
