<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Equipment\Event\TransformEquipmentEvent;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class GameEquipmentService implements GameEquipmentServiceInterface
{
    private EntityManagerInterface $entityManager;
    private GameEquipmentRepository $repository;
    private EquipmentServiceInterface $equipmentService;
    private RandomServiceInterface $randomService;
    private EventServiceInterface $eventService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameEquipmentRepository $repository,
        EquipmentServiceInterface $equipmentService,
        RandomServiceInterface $randomService,
        EventServiceInterface $eventService,
        StatusServiceInterface $statusService,
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->equipmentService = $equipmentService;
        $this->randomService = $randomService;
        $this->eventService = $eventService;
        $this->statusService = $statusService;
    }

    public function persist(GameEquipment $equipment): GameEquipment
    {
        $this->entityManager->persist($equipment);
        $this->entityManager->flush();

        return $equipment;
    }

    public function delete(GameEquipment $equipment): void
    {
        $this->entityManager->remove($equipment);
        $this->entityManager->flush();
    }

    public function findById(int $id): ?GameEquipment
    {
        $equipment = $this->repository->find($id);

        return $equipment instanceof GameEquipment ? $equipment : null;
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection
    {
        return new ArrayCollection($this->repository->findByNameAndDaedalus($name, $daedalus));
    }

    public function findByOwner(Player $player): ArrayCollection
    {
        return new ArrayCollection($this->repository->findBy(['owner' => $player]));
    }

    public function createGameEquipmentFromName(
        string $equipmentName,
        EquipmentHolderInterface $equipmentHolder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment {
        $config = $this->equipmentService->findByNameAndDaedalus($equipmentName, $equipmentHolder->getPlace()->getDaedalus());

        return $this->createGameEquipment($config, $equipmentHolder, $reasons, $time, $visibility);
    }

    public function createGameEquipment(
        EquipmentConfig $equipmentConfig,
        EquipmentHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment {
        $equipment = $this->getEquipmentFromConfig($equipmentConfig, $holder, $reasons);

        $event = new EquipmentEvent(
            $equipment,
            true,
            $visibility,
            $reasons,
            $time
        );
        $this->eventService->callEvent($event, EquipmentEvent::EQUIPMENT_CREATED);

        return $equipment;
    }

    private function getEquipmentFromConfig(
        EquipmentConfig $config,
        EquipmentHolderInterface $holder,
        array $reasons
    ): GameEquipment {
        if ($config instanceof ItemConfig) {
            $gameEquipment = $config->createGameItem($holder);
        } else {
            $gameEquipment = $config->createGameEquipment($holder->getPlace());
        }

        if ($config->isPersonal()) {
            if (!($holder instanceof Player)) {
                throw new \Exception("holder of this gameEquipment {$gameEquipment->getName()} should be a player");
            }
            $gameEquipment->setOwner($holder);
        }

        $this->persist($gameEquipment);

        $this->initMechanics($gameEquipment, $holder->getPlace()->getDaedalus(), $reasons);

        return $gameEquipment;
    }

    public function transformGameEquipmentToEquipmentWithName(
        string $resultName,
        GameEquipment $input,
        EquipmentHolderInterface $holder,
        array $reasons,
        \DateTime $time,
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment {
        $config = $this->equipmentService->findByNameAndDaedalus($resultName, $holder->getPlace()->getDaedalus());

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

        if ($gameEquipment->getEquipment()->isFireDestroyable()
            && $this->randomService->isSuccessful($this->getGameConfig($gameEquipment)->getDifficultyConfig()->getEquipmentFireBreakRate())
        ) {
            $equipmentEvent = new EquipmentEvent(
                $gameEquipment,
                false,
                VisibilityEnum::PUBLIC,
                [EventEnum::FIRE],
                $date
            );
            $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }

        if ($gameEquipment->getEquipment()->isFireBreakable()
            && !$gameEquipment->getStatusByName(EquipmentStatusEnum::BROKEN)
            && $this->randomService->isSuccessful($this->getGameConfig($gameEquipment)->getDifficultyConfig()->getEquipmentFireBreakRate())
        ) {
            $this->statusService->createStatusFromName(
                EquipmentStatusEnum::BROKEN,
                $gameEquipment,
                [EventEnum::FIRE],
                $date,
                null,
                VisibilityEnum::PUBLIC
            );

            $this->persist($gameEquipment);
        }
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

    private function movePatrolShipContentToSpace(GameEquipment $patrolShip, ?Player $player, array $tags): void
    {
        /** @var Daedalus $daedalus */
        $daedalus = $patrolShip->getDaedalus();
        /** @var Place $patrolShipPlace */
        $patrolShipPlace = $daedalus->getPlaceByName($patrolShip->getName());
        if (!$patrolShipPlace instanceof Place) {
            throw new \LogicException('Patrol ship holder should be a place');
        }

        /** @var GameEquipment $item */
        foreach ($patrolShipPlace->getEquipments() as $item) {
            $moveEquipmentEvent = new MoveEquipmentEvent(
                equipment: $item,
                newHolder: $daedalus->getSpace(),
                author: $player,
                visibility: VisibilityEnum::HIDDEN,
                tags: $tags,
                time: new \DateTime(),
            );
            $this->eventService->callEvent($moveEquipmentEvent, EquipmentEvent::CHANGE_HOLDER);
        }
    }

    private function initMechanics(GameEquipment $gameEquipment, Daedalus $daedalus, array $reasons): GameEquipment
    {
        /** @var EquipmentMechanic $mechanic */
        foreach ($gameEquipment->getEquipment()->getMechanics() as $mechanic) {
            if ($mechanic instanceof Plant) {
                if (!in_array(EventEnum::CREATE_DAEDALUS, $reasons)) {
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

        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::PLANT_YOUNG,
            $gameEquipment,
            [EquipmentEvent::EQUIPMENT_CREATED],
            new \DateTime()
        );

        return $gameEquipment;
    }

    private function initDocument(GameEquipment $gameEquipment, Document $document): GameEquipment
    {
        if (!$document instanceof Document) {
            throw new \LogicException('Parameter is not a document');
        }

        $status = $this->statusService->createStatusFromName(
            EquipmentStatusEnum::DOCUMENT_CONTENT,
            $gameEquipment,
            [EquipmentEvent::EQUIPMENT_CREATED],
            new \DateTime()
        );

        $status->setContent($document->getContent());

        return $gameEquipment;
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getHolder()->getDaedalus()->getGameConfig();
    }

    private function persistEntities(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
    }
}
