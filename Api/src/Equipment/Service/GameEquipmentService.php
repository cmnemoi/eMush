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
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\EquipmentInitEvent;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\ChargeStatusEvent;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GameEquipmentService implements GameEquipmentServiceInterface
{
    private EntityManagerInterface $entityManager;
    private GameEquipmentRepository $repository;
    private EquipmentServiceInterface $equipmentService;
    private EquipmentEffectServiceInterface $equipmentEffectService;
    private RandomServiceInterface $randomService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameEquipmentRepository $repository,
        EquipmentServiceInterface $equipmentService,
        EquipmentEffectServiceInterface $equipmentEffectService,
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->equipmentService = $equipmentService;
        $this->equipmentEffectService = $equipmentEffectService;
        $this->randomService = $randomService;
        $this->eventDispatcher = $eventDispatcher;
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
        return $this->repository->find($id);
    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection
    {
        return new ArrayCollection($this->repository->findByNameAndDaedalus($name, $daedalus));
    }

    public function createGameEquipmentFromName(
        string $equipmentName,
        EquipmentHolderInterface $equipmentHolder,
        string $reason,
        \DateTime $time
    ): GameEquipment {
        $equipment = $this->equipmentService->findByNameAndDaedalus($equipmentName, $equipmentHolder->getPlace()->getDaedalus());

        return $this->createGameEquipment($equipment, $equipmentHolder, $reason, $time);
    }

    public function createGameEquipment(
        EquipmentConfig $equipment,
        EquipmentHolderInterface $holder,
        string $reason,
        \DateTime $time
    ): GameEquipment {
        if ($equipment instanceof ItemConfig) {
            $gameEquipment = $equipment->createGameItem();
        } else {
            $gameEquipment = $equipment->createGameEquipment();
        }

        $gameEquipment->setHolder($holder);
        $this->persist($gameEquipment);

        $gameEquipment = $this->initMechanics($gameEquipment, $holder->getPlace()->getDaedalus());

        $equipmentEvent = new EquipmentInitEvent($gameEquipment, $equipment, $reason, $time);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentInitEvent::NEW_EQUIPMENT);

        return $gameEquipment;
    }

    private function initMechanics(GameEquipment $gameEquipment, Daedalus $daedalus): GameEquipment
    {
        /** @var EquipmentMechanic $mechanic */
        foreach ($gameEquipment->getEquipment()->getMechanics() as $mechanic) {
            switch ($mechanic->getMechanic()) {
                case EquipmentMechanicEnum::PLANT:
                    $this->initPlant($gameEquipment, $mechanic, $daedalus);
                    break;
                case EquipmentMechanicEnum::DOCUMENT:
                    if ($mechanic instanceof Document && $mechanic->getContent()) {
                        $this->initDocument($gameEquipment, $mechanic);
                    }
                    break;
            }
        }

        return $gameEquipment;
    }

    private function initPlant(GameEquipment $gameEquipment, EquipmentMechanic $plant, Daedalus $daedalus): GameEquipment
    {
        if (!$plant instanceof Plant) {
            throw new \LogicException('Parameter is not a plant');
        }

        $statusEvent = new ChargeStatusEvent(EquipmentStatusEnum::PLANT_YOUNG, $gameEquipment, EquipmentEvent::EQUIPMENT_CREATED, new \DateTime());
        $statusEvent->setInitCharge(1);
        $statusEvent->setThreshold($this->equipmentEffectService->getPlantEffect($plant, $daedalus)->getMaturationTime());

        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);

        return $gameEquipment;
    }

    private function initDocument(GameEquipment $gameEquipment, $document): GameEquipment
    {
        if (!$document instanceof Document) {
            throw new \LogicException('Parameter is not a document');
        }

        // @TODO rework when better handling Daedalus creation
        $statusEvent = new StatusEvent(EquipmentStatusEnum::DOCUMENT_CONTENT, $gameEquipment, EquipmentEvent::EQUIPMENT_CREATED, new \DateTime());
        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);

        return $gameEquipment;
    }

    public function handleBreakFire(GameEquipment $gameEquipment, \DateTime $date): void
    {
        if ($gameEquipment instanceof Door) {
            return;
        }

        if ($gameEquipment->getEquipment()->isFireDestroyable() &&
            $this->randomService->isSuccessful($this->getGameConfig($gameEquipment)->getDifficultyConfig()->getEquipmentFireBreakRate())
        ) {
            $equipmentEvent = new EquipmentEvent(
                $gameEquipment,
                $gameEquipment->getPlace(),
                VisibilityEnum::PUBLIC,
                EventEnum::FIRE,
                $date
            );
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }

        if ($gameEquipment->getEquipment()->isFireBreakable() &&
            !$gameEquipment->getStatusByName(EquipmentStatusEnum::BROKEN) &&
            $this->randomService->isSuccessful($this->getGameConfig($gameEquipment)->getDifficultyConfig()->getEquipmentFireBreakRate())
        ) {
            $statusEvent = new StatusEvent(
                EquipmentStatusEnum::BROKEN,
                $gameEquipment,
                EventEnum::FIRE,
                $date
            );
            $statusEvent->setVisibility(VisibilityEnum::PUBLIC);
            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);

            $this->persist($gameEquipment);
        }
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getEquipment()->getGameConfig();
    }
}
