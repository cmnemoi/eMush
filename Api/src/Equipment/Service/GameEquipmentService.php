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
use Mush\Equipment\Event\TransformEquipmentEvent;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;

class GameEquipmentService implements GameEquipmentServiceInterface
{
    private EntityManagerInterface $entityManager;
    private GameEquipmentRepository $repository;
    private EquipmentServiceInterface $equipmentService;
    private RandomServiceInterface $randomService;
    private EventServiceInterface $eventService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameEquipmentRepository $repository,
        EquipmentServiceInterface $equipmentService,
        RandomServiceInterface $randomService,
        EventServiceInterface $eventService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->equipmentService = $equipmentService;
        $this->randomService = $randomService;
        $this->eventService = $eventService;
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
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment {
        $config = $this->equipmentService->findByNameAndDaedalus($equipmentName, $equipmentHolder->getPlace()->getDaedalus());

        return $this->createGameEquipment($config, $equipmentHolder, $reasons, $visibility);
    }

    public function createGameEquipment(
        EquipmentConfig $equipmentConfig,
        EquipmentHolderInterface $holder,
        array $reasons,
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment {
        $equipment = $this->getEquipmentFromConfig($equipmentConfig, $holder, $reasons);

        $event = new EquipmentEvent(
            $equipment,
            true,
            $visibility,
            $reasons,
            new \DateTime()
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
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment {
        $config = $this->equipmentService->findByNameAndDaedalus($resultName, $holder->getPlace()->getDaedalus());

        return $this->transformGameEquipmentToEquipment($config, $input, $holder, $reasons, $visibility);
    }

    public function transformGameEquipmentToEquipment(
        EquipmentConfig $resultConfig,
        GameEquipment $input,
        EquipmentHolderInterface $holder,
        array $reasons,
        string $visibility = VisibilityEnum::HIDDEN
    ): GameEquipment {
        $result = $this->getEquipmentFromConfig($resultConfig, $holder, $reasons);

        $equipmentEvent = new TransformEquipmentEvent(
            $result,
            $input,
            $visibility,
            $reasons,
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_TRANSFORM);

        return $result;
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

        $statusEvent = new StatusEvent(
            EquipmentStatusEnum::PLANT_YOUNG,
            $gameEquipment,
            [EquipmentEvent::EQUIPMENT_CREATED],
            new \DateTime()
        );

        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);

        return $gameEquipment;
    }

    private function initDocument(GameEquipment $gameEquipment, Document $document): GameEquipment
    {
        if (!$document instanceof Document) {
            throw new \LogicException('Parameter is not a document');
        }

        $statusEvent = new StatusEvent(
            EquipmentStatusEnum::DOCUMENT_CONTENT,
            $gameEquipment,
            [EquipmentEvent::EQUIPMENT_CREATED],
            new \DateTime()
        );

        $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);

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
                false,
                VisibilityEnum::PUBLIC,
                [EventEnum::FIRE],
                $date
            );
            $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }

        if ($gameEquipment->getEquipment()->isFireBreakable() &&
            !$gameEquipment->getStatusByName(EquipmentStatusEnum::BROKEN) &&
            $this->randomService->isSuccessful($this->getGameConfig($gameEquipment)->getDifficultyConfig()->getEquipmentFireBreakRate())
        ) {
            $statusEvent = new StatusEvent(
                EquipmentStatusEnum::BROKEN,
                $gameEquipment,
                [EventEnum::FIRE],
                $date
            );
            $statusEvent->setVisibility(VisibilityEnum::PUBLIC);
            $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);

            $this->persist($gameEquipment);
        }
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getHolder()->getDaedalus()->getGameConfig();
    }
}
