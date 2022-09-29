<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EquipmentFactory implements EquipmentFactoryInterface
{

    private GameEquipmentRepository $repository;
    private EquipmentServiceInterface $service;
    private RandomServiceInterface $random;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        GameEquipmentRepository   $repository,
        EquipmentServiceInterface $service,
        RandomServiceInterface    $random,
        EventDispatcherInterface  $eventDispatcher
    ) {
        $this->repository = $repository;
        $this->service = $service;
        $this->random = $random;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function deleteEquipment(Equipment $equipment, string $reason, string $visibility): void
    {
        $this->dispatchDeletionEvent($equipment, $reason, $visibility);
        $this->service->delete($equipment);
    }

    private function dispatchDeletionEvent(Equipment $equipment, string $reason, string $visibility) {

    }

    public function findByNameAndDaedalus(string $name, Daedalus $daedalus): ArrayCollection
    {
        return new ArrayCollection($this->repository->findByNameAndDaedalus($name, $daedalus));
    }

    public function createGameEquipmentFromName(
        string                   $name,
        EquipmentHolderInterface $holder,
        string                   $visibility,
        string                   $reason
    ): Equipment {
        $config = $this->service->findByNameAndDaedalus($name, $holder->getPlace()->getDaedalus());
        return $this->createGameEquipment($config, $holder, $visibility, $reason);
    }

    public function createGameEquipment(
        EquipmentConfig          $config,
        EquipmentHolderInterface $holder,
        string $visibility,
        string                   $reason,
    ): Equipment {
        if ($config instanceof ItemConfig) {
            $equipment = $config->createGameItem();
            $equipment->setHolder($holder);
        } else {
            $equipment = $config->createGameEquipment();
            $equipment->setHolder($holder->getPlace());
        }

        $this->service->persist($equipment);
        $this->initMechanics($equipment, $holder->getPlace()->getDaedalus(), $reason);

        if ($config->isPersonal()) {
            if (!($holder instanceof Player)) {
                throw new \LogicException('holder should be a player');
            }

            $equipment->setOwner($holder);
        }

        $this->dispatchCreationEvent($equipment, $visibility, $reason);
        return $equipment;
    }

    private function dispatchCreationEvent(Equipment $equipment, string $visibility, string $reason) {
        $event = new EquipmentEvent(
            $equipment,
            true,
            $visibility,
            $reason,
            new \DateTime()
        );

        $this->eventDispatcher->dispatch($event, EquipmentEvent::EQUIPMENT_CREATED);
    }

    private function initMechanics(Equipment $equipment, Daedalus $daedalus, string $reason): void
    {
        /** @var EquipmentMechanic $mechanic */
        foreach ($equipment->getConfig()->getMechanics() as $mechanic) {
            if ($mechanic instanceof Plant) {
                if ($reason !== EventEnum::CREATE_DAEDALUS) {
                    $this->initPlant($equipment, $mechanic, $daedalus);
                }
            } elseif ($mechanic instanceof Document && $mechanic->getContent()) {
                $this->initDocument($equipment, $mechanic);
            }
        }
    }

    private function initPlant(Equipment $equipment, EquipmentMechanic $plant): void
    {
        if (!$plant instanceof Plant) {
            throw new \LogicException('Parameter is not a plant');
        }

        $statusEvent = new StatusEvent(
            EquipmentStatusEnum::PLANT_YOUNG,
            $equipment,
            EquipmentEvent::EQUIPMENT_CREATED,
            new \DateTime()
        );

        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
    }

    private function initDocument(Equipment $equipment, $document): void
    {
        if (!$document instanceof Document) {
            throw new \LogicException('Parameter is not a document');
        }

        $statusEvent = new StatusEvent(
            EquipmentStatusEnum::DOCUMENT_CONTENT,
            $equipment,
            EquipmentEvent::EQUIPMENT_CREATED,
            new \DateTime()
        );

        $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
    }

    // @TODO NOT BELONG HERE
    public function handleBreakFire(Equipment $gameEquipment, \DateTime $date): void
    {
        if ($gameEquipment instanceof Door) {
            // @THINK add a possible door fire break ?
            return;
        }

        if ($gameEquipment->getConfig()->isFireDestroyable() &&
            $this->random->isSuccessful($this->getGameConfig($gameEquipment)->getDifficultyConfig()->getEquipmentFireBreakRate())
        ) {
            $equipmentEvent = new EquipmentEvent(
                $gameEquipment,
                false,
                VisibilityEnum::PUBLIC,
                EventEnum::FIRE,
                $date
            );
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }

        if ($gameEquipment->getConfig()->isFireBreakable() &&
            !$gameEquipment->getStatusByName(EquipmentStatusEnum::BROKEN) &&
            $this->random->isSuccessful($this->getGameConfig($gameEquipment)->getDifficultyConfig()->getEquipmentFireBreakRate())
        ) {
            $statusEvent = new StatusEvent(
                EquipmentStatusEnum::BROKEN,
                $gameEquipment,
                EventEnum::FIRE,
                $date
            );
            $statusEvent->setVisibility(VisibilityEnum::PUBLIC);
            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
        }
    }

    private function getGameConfig(Equipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getConfig()->getGameConfig();
    }
}
