<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\DroneInfo;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Equipment\Enum\DroneNicknameEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Equipment\Event\TransformEquipmentEvent;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class GameEquipmentService implements GameEquipmentServiceInterface
{
    private EntityManagerInterface $entityManager;
    private GameEquipmentRepositoryInterface $repository;
    private DamageEquipmentServiceInterface $damageEquipmentService;
    private EquipmentServiceInterface $equipmentService;
    private RandomServiceInterface $randomService;
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;
    private EquipmentEffectServiceInterface $equipmentEffectService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameEquipmentRepositoryInterface $repository,
        DamageEquipmentServiceInterface $damageEquipmentService,
        EquipmentServiceInterface $equipmentService,
        RandomServiceInterface $randomService,
        EventServiceInterface $eventService,
        StatusServiceInterface $statusService,
        EquipmentEffectServiceInterface $equipmentEffectService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->damageEquipmentService = $damageEquipmentService;
        $this->equipmentService = $equipmentService;
        $this->randomService = $randomService;
        $this->eventService = $eventService;
        $this->statusService = $statusService;
        $this->equipmentEffectService = $equipmentEffectService;
    }

    public function persist(GameEquipment $equipment): GameEquipment
    {
        $this->repository->save($equipment);

        return $equipment;
    }

    public function delete(GameEquipment $equipment): void
    {
        $this->repository->delete($equipment);
    }

    public function findById(int $id): ?GameEquipment
    {
        $equipment = $this->repository->findById($id);

        return $equipment instanceof GameEquipment ? $equipment : null;
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection
    {
        return new ArrayCollection($this->repository->findByNameAndDaedalus($name, $daedalus));
    }

    public function findEquipmentsByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection
    {
        return new ArrayCollection($this->repository->findEquipmentsByNameAndDaedalus($name, $daedalus));
    }

    public function findPatrolShipsByDaedalus(Daedalus $daedalus): ArrayCollection
    {
        $patrolShips = [];
        foreach (EquipmentEnum::getPatrolShips() as $configName) {
            $patrolShips = array_merge($patrolShips, $this->repository->findEquipmentsByNameAndDaedalus($configName, $daedalus));
        }

        return new ArrayCollection($patrolShips);
    }

    public function findByDaedalus(Daedalus $daedalus): ArrayCollection
    {
        return new ArrayCollection($this->repository->findByDaedalus($daedalus));
    }

    public function findByOwner(Player $player): ArrayCollection
    {
        return new ArrayCollection($this->repository->findByOwner($player));
    }

    public function findEquipmentByNameAndPlace(string $name, Place $place, int $quantity): ArrayCollection
    {
        return new ArrayCollection($this->repository->findEquipmentByNameAndPlace($name, $place, $quantity));
    }

    public function findEquipmentByNameAndPlayer(string $name, Player $player, int $quantity): ArrayCollection
    {
        return new ArrayCollection($this->repository->findEquipmentByNameAndPlayer($name, $player, $quantity));
    }

    public function createGameEquipmentFromName(
        string $equipmentName,
        EquipmentHolderInterface $equipmentHolder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN,
        ?Player $author = null
    ): GameEquipment {
        $config = $this->equipmentService->findByNameAndDaedalus($equipmentName, $equipmentHolder->getPlace()->getDaedalus());

        return $this->createGameEquipment($config, $equipmentHolder, $reasons, $time, $visibility, $author);
    }

    /**
     * @return array<GameEquipment>
     */
    public function createGameEquipmentsFromName(
        string $equipmentName,
        EquipmentHolderInterface $equipmentHolder,
        int $quantity,
        array $reasons = [],
        \DateTime $time = new \DateTime(),
        string $visibility = VisibilityEnum::HIDDEN,
        ?Player $author = null
    ): array {
        $config = $this->equipmentService->findByNameAndDaedalus($equipmentName, $equipmentHolder->getPlace()->getDaedalus());

        $equipments = [];
        for ($i = 0; $i < $quantity; ++$i) {
            $equipments[] = $this->createGameEquipment($config, $equipmentHolder, $reasons, $time, $visibility, $author);
        }

        return $equipments;
    }

    public function createGameEquipment(
        EquipmentConfig $equipmentConfig,
        EquipmentHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN,
        ?Player $author = null,
        ?string $patrolShipName = null
    ): GameEquipment {
        $equipment = $this->getEquipmentFromConfig($equipmentConfig, $holder, $reasons, $patrolShipName);

        $event = new EquipmentEvent(
            $equipment,
            true,
            $visibility,
            $reasons,
            $time
        );
        $event->setAuthor($author);
        $this->eventService->callEvent($event, EquipmentEvent::EQUIPMENT_CREATED);

        return $equipment;
    }

    public function transformGameEquipmentToEquipmentWithName(
        string $newEquipmentName,
        GameEquipment $input,
        EquipmentHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment {
        $config = $this->equipmentService->findByNameAndDaedalus($newEquipmentName, $holder->getPlace()->getDaedalus());

        return $this->transformGameEquipmentToEquipment($config, $input, $holder, $reasons, $time, $visibility);
    }

    public function transformGameEquipmentToEquipment(
        EquipmentConfig $resultConfig,
        GameEquipment $input,
        EquipmentHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment {
        $result = $this->getEquipmentFromConfig($resultConfig, $holder, $reasons);

        $equipmentEvent = new TransformEquipmentEvent(
            $result,
            $input,
            $visibility,
            $reasons,
            $time
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_TRANSFORM);

        return $result;
    }

    public function handleBreakFire(GameEquipment $gameEquipment, \DateTime $date): void
    {
        if ($gameEquipment instanceof Door) {
            return;
        }

        if ($gameEquipment->hasStatus(EquipmentStatusEnum::FIREFIGHTER_DRONE_UPGRADE)) {
            return;
        }

        if (!$gameEquipment->canBeDamaged()) {
            return;
        }

        if ($gameEquipment->getStatusByName(EquipmentStatusEnum::BROKEN)) {
            return;
        }

        if (!$this->randomService->isSuccessful($this->getGameConfig($gameEquipment)->getDifficultyConfig()->getEquipmentFireBreakRate())) {
            return;
        }

        $this->damageEquipmentService->execute(
            gameEquipment: $gameEquipment,
            tags: [EventEnum::FIRE, $gameEquipment->getName()],
            time: $date,
            visibility: VisibilityEnum::PUBLIC
        );
    }

    public function handlePatrolShipDestruction(GameEquipment $patrolShip, ?Player $player, array $tags): void
    {
        $destroyPatrolShipEvent = new InteractWithEquipmentEvent(
            $patrolShip,
            $player,
            VisibilityEnum::HIDDEN,
            $tags,
            new \DateTime(),
        );
        $this->eventService->callEvent($destroyPatrolShipEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $this->movePatrolShipContentToSpace($patrolShip, $player, $tags);
    }

    public function moveEquipmentTo(
        GameEquipment $equipment,
        EquipmentHolderInterface $newHolder,
        string $visibility = VisibilityEnum::HIDDEN,
        array $tags = [],
        \DateTime $time = new \DateTime(),
        ?Player $author = null
    ): void {
        $moveEquipmentEvent = new MoveEquipmentEvent(
            equipment: $equipment,
            newHolder: $newHolder,
            author: $author,
            visibility: $visibility,
            tags: $tags,
            time: $time,
        );
        $this->eventService->callEvent($moveEquipmentEvent, EquipmentEvent::CHANGE_HOLDER);
    }

    public function findGameEquipmentConfigFromNameAndDaedalus(
        string $equipmentName,
        Daedalus $daedalus
    ): EquipmentConfig {
        return $this->equipmentService->findByNameAndDaedalus($equipmentName, $daedalus);
    }

    private function getEquipmentFromConfig(
        EquipmentConfig $config,
        EquipmentHolderInterface $holder,
        array $reasons,
        ?string $patrolShipName = null
    ): GameEquipment {
        $gameEquipment = $config->createGameEquipment($holder);

        if ($config->isPersonal()) {
            if (!$holder instanceof Player) {
                throw new \Exception("holder of this gameEquipment {$gameEquipment->getName()} should be a player");
            }
            $gameEquipment->setOwner($holder);
        }

        if ($gameEquipment instanceof Drone) {
            $this->initDrone($gameEquipment);
        }

        if ($gameEquipment instanceof SpaceShip && $patrolShipName !== null) {
            $gameEquipment->setPatrolShipName($patrolShipName);
        }

        $this->persist($gameEquipment);

        $this->initMechanics($gameEquipment, $holder->getPlace()->getDaedalus(), $reasons);

        return $gameEquipment;
    }

    private function movePatrolShipContentToSpace(GameEquipment $patrolShip, ?Player $player, array $tags): void
    {
        /** @var Daedalus $daedalus */
        $daedalus = $patrolShip->getDaedalus();

        $patrolShipPlace = $daedalus->getPlaceByNameOrThrow($patrolShip->getName());

        /** @var GameEquipment $item */
        foreach ($patrolShipPlace->getEquipments() as $item) {
            $this->moveEquipmentTo(
                equipment: $item,
                newHolder: $daedalus->getSpace(),
                visibility: VisibilityEnum::HIDDEN,
                tags: $tags,
                time: new \DateTime(),
                author: $player
            );
        }
    }

    private function initMechanics(GameEquipment $gameEquipment, Daedalus $daedalus, array $reasons): GameEquipment
    {
        /** @var EquipmentMechanic $mechanic */
        foreach ($gameEquipment->getEquipment()->getMechanics() as $mechanic) {
            if ($mechanic instanceof Plant) {
                if (!\in_array(EventEnum::CREATE_DAEDALUS, $reasons, true)) {
                    $this->initPlant($gameEquipment, $mechanic, $daedalus);
                }
            } elseif ($mechanic instanceof Document && $mechanic->getContent()) {
                $this->initDocument($gameEquipment, $mechanic);
            }
        }

        return $gameEquipment;
    }

    private function initPlant(GameEquipment $gameEquipment, EquipmentMechanic $plant, Daedalus $daedalus): GameEquipment
    {
        if (!$plant instanceof Plant) {
            throw new \LogicException('Parameter is not a plant');
        }

        $plantEffect = $this->equipmentEffectService->getPlantEffect($plant, $daedalus);

        /** @var ChargeStatus $status */
        $status = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::PLANT_YOUNG,
            $gameEquipment,
            [EquipmentEvent::EQUIPMENT_CREATED],
            new \DateTime()
        );

        $status->getVariableByName(EquipmentStatusEnum::PLANT_YOUNG)->setMaxValue($plantEffect->getMaturationTime());
        $this->statusService->persist($status);

        return $gameEquipment;
    }

    private function initDocument(GameEquipment $gameEquipment, Document $document): GameEquipment
    {
        /** @var ContentStatus $status */
        $status = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::DOCUMENT_CONTENT,
            $gameEquipment,
            [EquipmentEvent::EQUIPMENT_CREATED],
            new \DateTime()
        );

        $status->setContent($document->getContent());

        return $gameEquipment;
    }

    private function initDrone(Drone $drone): Drone
    {
        $droneInfo = new DroneInfo(
            $drone,
            nickName: $this->randomService->random(1, \count(DroneNicknameEnum::cases())),
            serialNumber: $this->randomService->random(1, 99)
        );
        $this->entityManager->persist($droneInfo);

        $drone->setDroneInfo($droneInfo);

        return $drone;
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getHolder()->getDaedalus()->getGameConfig();
    }
}
