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
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\HunterStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
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

    public function killHunter(Hunter $hunter): void
    {
        $daedalus = $hunter->getDaedalus();

        $this->dropScrap($hunter);

        $daedalus->getDaedalusInfo()->getClosedDaedalus()->incrementNumberOfHuntersKilled();

        $daedalus->getAttackingHunters()->removeElement($hunter);
        $this->entityManager->remove($hunter);
        $this->persist([$daedalus]);
    }

    public function makeHuntersShoot(HunterCollection $attackingHunters): void
    {
        /** @var Hunter $hunter */
        foreach ($attackingHunters as $hunter) {
            if (!$hunter->canShoot()) {
                continue;
            }

            $successRate = $hunter->getHunterConfig()->getHitChance();
            if (!$this->randomService->isSuccessful($successRate)) {
                continue;
            }

            $this->makeHunterShoot($hunter);

            // @TODO test that the target doesn't change until hunter has made a successful shot
            $this->selectHunterTarget($hunter);
            if (!$hunter->getTarget()->isInBattle()) {
                continue;
            }

            // hunter gets a truce cycle after shooting
            $this->createHunterTruceCycleStatus($hunter);

            // destroy asteroid if it has shot
            if ($hunter->getName() === HunterEnum::ASTEROID) {
                $this->killHunter($hunter);
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
            if ($hunterPoints < $hunterProbaCollection->min()) {
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
            if ($maxPerWave && $wave->getAllHuntersByType($hunter->getName())->count() > $maxPerWave) {
                $hunterTypes->removeElement($hunterNameToCreate);
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

    private function createHunterTruceCycleStatus(Hunter $hunter): void
    {
        $truceCycleStatus = $hunter->getHunterConfig()->getInitialStatuses()->filter(
            fn (StatusConfig $statusConfig) => $statusConfig->getStatusName() === HunterStatusEnum::HUNTER_CHARGE
        )->first();

        if (!$truceCycleStatus) {
            throw new \Exception('Hunter config should have a HUNTER_CHARGE status config');
        }
        $this->statusService->createStatusFromConfig(
            $truceCycleStatus,
            $hunter,
            [AbstractHunterEvent::HUNTER_SHOT],
            new \DateTime()
        );
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

        $hunterTarget = $hunter->getTarget()->getTargetEntity();

        // @TODO: handle hunter and merchant targets in the future
        switch ($hunterTarget) {
            case $hunterTarget instanceof Daedalus:
                $this->shootAtDaedalus($hunterTarget, $damage);
                break;
            case $hunterTarget instanceof GameEquipment:
                $this->shootAtPatrolShip($hunterTarget, $damage);
                break;
            case $hunterTarget instanceof Player:
                $this->shootAtPlayer($hunterTarget, $damage);
                break;
            default:
                throw new \Exception("Unknown hunter target {$hunter->getTarget()->getType()}");
        }
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
        $patrolShipsInBattle = $patrolShips->filter(fn (GameEquipment $patrolShip) => $patrolShip->getPlace()->getType() === PlaceTypeEnum::PATROL_SHIP);

        if (!$patrolShipsInBattle->isEmpty()) {
            $successRate = $targetProbabilities->get(HunterTargetEnum::PATROL_SHIP);
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

        // if there is no player in battle, remove player target from probabilities to draw
        $playersInBattle = $hunter->getDaedalus()->getPlayers()->getPlayerAlive()->filter(fn ($player) => $player->getPlace()->getType() === PlaceTypeEnum::PATROL_SHIP);
        if (!$playersInBattle->isEmpty()) {
            $successRate = $targetProbabilities->get(HunterTargetEnum::PLAYER);
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

    private function shootAtPatrolShip(GameEquipment $patrolShip, int $damage): void
    {
        /** @var ChargeStatus $patrolShipArmor */
        $patrolShipArmor = $patrolShip->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);

        $this->statusService->updateCharge(
            chargeStatus: $patrolShipArmor,
            delta: -$damage,
            tags: [AbstractHunterEvent::HUNTER_SHOT],
            time: new \DateTime()
        );

        // @TODO send an event to destroy patrol ship if no armor left
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
