<?php

namespace Mush\Equipment\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Charged;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Enum\DoorEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GameEquipmentService implements GameEquipmentServiceInterface
{
    private EntityManagerInterface $entityManager;
    private GameEquipmentRepository $repository;
    private EquipmentServiceInterface $equipmentService;
    private StatusServiceInterface $statusService;
    private EquipmentEffectServiceInterface $equipmentEffectService;
    private RandomServiceInterface $randomService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameEquipmentRepository $repository,
        EquipmentServiceInterface $equipmentService,
        StatusServiceInterface $statusService,
        EquipmentEffectServiceInterface $equipmentEffectService,
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->equipmentService = $equipmentService;
        $this->statusService = $statusService;
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

    public function createGameEquipmentFromName(string $equipmentName, Daedalus $daedalus): GameEquipment
    {
        $equipment = $this->equipmentService->findByNameAndDaedalus($equipmentName, $daedalus);

        return $this->createGameEquipment($equipment, $daedalus);
    }

    public function createGameEquipment(EquipmentConfig $equipment, Daedalus $daedalus): GameEquipment
    {
        if ($equipment instanceof ItemConfig) {
            $gameEquipment = $equipment->createGameItem();
        } else {
            $gameEquipment = $equipment->createGameEquipment();
        }

        if ($equipment->isAlienArtifact()) {
            $this->initStatus($gameEquipment, EquipmentStatusEnum::ALIEN_ARTEFACT);
        }
        if ($equipment instanceof ItemConfig && $equipment->isHeavy()) {
            $this->initStatus($gameEquipment, EquipmentStatusEnum::HEAVY);
        }

        $gameEquipment = $this->initMechanics($gameEquipment, $daedalus);

        return $this->persist($gameEquipment);
    }

    private function initMechanics(GameEquipment $gameEquipment, Daedalus $daedalus): GameEquipment
    {
        /** @var EquipmentMechanic $mechanic */
        foreach ($gameEquipment->getEquipment()->getMechanics() as $mechanic) {
            switch ($mechanic->getMechanic()) {
                case EquipmentMechanicEnum::PLANT:
                    $this->initPlant($gameEquipment, $mechanic, $daedalus);
                    break;
                case EquipmentMechanicEnum::CHARGED:
                    $this->initCharged($gameEquipment, $mechanic);
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

        $this->statusService->createChargeEquipmentStatus(
            EquipmentStatusEnum::PLANT_YOUNG,
            $gameEquipment,
            ChargeStrategyTypeEnum::GROWING_PLANT,
            VisibilityEnum::PUBLIC,
            0,
            $this->equipmentEffectService->getPlantEffect($plant, $daedalus)->getMaturationTime()
        );

        return $gameEquipment;
    }

    private function initCharged(GameEquipment $gameEquipment, $charged): GameEquipment
    {
        if (!$charged instanceof Charged) {
            throw new \LogicException('Parameter is not a charged mechanic');
        }

        $chargeStatus = $this->statusService->createChargeEquipmentStatus(
            EquipmentStatusEnum::CHARGES,
            $gameEquipment,
            VisibilityEnum::PUBLIC,
            $charged->getChargeStrategy(),
            $charged->getStartCharge(),
            $charged->getMaxCharge()
        );

        if (!$charged->isVisible()) {
            $chargeStatus->setVisibility(VisibilityEnum::HIDDEN);
        }

        return $gameEquipment;
    }

    private function initDocument(GameEquipment $gameEquipment, $document): GameEquipment
    {
        if (!$document instanceof Document) {
            throw new \LogicException('Parameter is not a document');
        }

        $contentStatus = new ContentStatus();
        $contentStatus
            ->setName(EquipmentStatusEnum::DOCUMENT_CONTENT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setGameEquipment($gameEquipment)
            ->setContent($document->getContent())
        ;

        return $gameEquipment;
    }

    private function initStatus(GameEquipment $gameEquipment, string $statusName): GameEquipment
    {
        $this->statusService->createCoreEquipmentStatus(
            $statusName,
            $gameEquipment
        );

        return $gameEquipment;
    }

    //Implement accessibility to Equipment (for tool and gear)
    public function getOperationalEquipmentsByName(string $equipmentName, Player $player, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection
    {
        //reach can be set to inventory, shelve, shelve only or any room of the Daedalus
        return $player->getReachableEquipmentsByName($equipmentName, $reach)
            ->filter(fn (GameEquipment $gameEquipment) => $this->isOperational($gameEquipment))
            ;
    }

    public function isOperational(GameEquipment $gameEquipment): bool
    {
        /** @var ?ChargeStatus $chargedStatus */
        $chargedStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::CHARGES);
        if ($chargedStatus) {
            return !($gameEquipment->getStatusByName(EquipmentStatusEnum::BROKEN)) && $chargedStatus->getCharge() > 0;
        }

        return !($gameEquipment->getStatusByName(EquipmentStatusEnum::BROKEN));
    }

    public function handleBreakCycle(GameEquipment $gameEquipment, \DateTime $date): void
    {
        if ($gameEquipment->getStatusByName(EquipmentStatusEnum::BROKEN) ||
            $gameEquipment instanceof GameItem) {
            return;
        }

        if (($gameEquipment instanceof Door &&
            !DoorEnum::isUnbreakable($gameEquipment->getName()) &&
            $this->randomService->isSuccessfull($this->getGameConfig($gameEquipment)->getDifficultyConfig()->getDoorBreakRate())) ||
            ($gameEquipment->getEquipment()->getBreakableRate() > 0 &&
            $this->randomService->isSuccessfull($this->getGameConfig($gameEquipment)->getDifficultyConfig()->getEquipmentBreakRate()))
            ) {
            $equipmentEvent = new EquipmentEvent($gameEquipment, VisibilityEnum::HIDDEN, $date);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_BROKEN);
        }

        $this->persist($gameEquipment);

        return;
    }

    public function handleBreakFire(GameEquipment $gameEquipment, \DateTime $date): void
    {
        if ($gameEquipment instanceof Door) {
            return;
        }

        if ($gameEquipment->getEquipment()->isFireDestroyable() &&
            $this->randomService->isSuccessfull($this->getGameConfig($gameEquipment)->getDifficultyConfig()->getEquipmentFireBreakRate())
        ) {
            $equipmentEvent = new EquipmentEvent($gameEquipment, VisibilityEnum::PUBLIC, $date);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }

        if ($gameEquipment->getEquipment()->isFireBreakable() &&
            !$gameEquipment->getStatusByName(EquipmentStatusEnum::BROKEN) &&
            $this->randomService->isSuccessfull($this->getGameConfig($gameEquipment)->getDifficultyConfig()->getEquipmentFireBreakRate())
        ) {
            $equipmentEvent = new EquipmentEvent($gameEquipment, VisibilityEnum::PUBLIC, $date);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_BROKEN);
            $this->persist($gameEquipment);
        }

        return;
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getEquipment()->getGameConfig();
    }
}
