<?php

namespace Mush\Equipment\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class PlantCycleHandler extends AbstractCycleHandler
{
    protected string $name = EquipmentMechanicEnum::PLANT;

    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;
    private EquipmentEffectServiceInterface $equipmentEffectService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        EquipmentEffectServiceInterface $equipmentEffectService,
        StatusServiceInterface $statusService
    ) {
        $this->eventService = $eventService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->randomService = $randomService;
        $this->equipmentEffectService = $equipmentEffectService;
        $this->statusService = $statusService;
    }

    public function handleNewCycle(GameEquipment $gameEquipment, \DateTime $dateTime): void
    {
        $daedalus = $gameEquipment->getDaedalus();
        $plantType = $gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PLANT);
        if (!$plantType instanceof Plant) {
            return;
        }

        $diseaseRate = $daedalus->getGameConfig()->getDifficultyConfig()->getPlantDiseaseRate();

        if ($this->randomService->isSuccessful($diseaseRate) && !$gameEquipment->hasStatus(EquipmentStatusEnum::PLANT_DISEASED)) {
            $this->statusService->createStatusFromName(
                EquipmentStatusEnum::PLANT_DISEASED,
                $gameEquipment,
                [EventEnum::NEW_CYCLE],
                new \DateTime()
            );
        }
    }

    public function handleNewDay(GameEquipment $gameEquipment, \DateTime $dateTime): void
    {
        $daedalus = $gameEquipment->getDaedalus();
        $plantType = $gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PLANT);
        if (!$plantType instanceof Plant) {
            return;
        }

        $plantEffect = $this->equipmentEffectService->getPlantEffect($plantType, $daedalus);

        $plantStatus = $gameEquipment->getStatuses();

        // If plant is young, dried or diseased, do not produce oxygen
        if ($plantStatus->filter(static fn (Status $status) => \in_array($status->getName(), [
            EquipmentStatusEnum::PLANT_DRY,
            EquipmentStatusEnum::PLANT_DISEASED,
            EquipmentStatusEnum::PLANT_YOUNG,
        ], true))->isEmpty()) {
            $this->addOxygen($gameEquipment, $plantEffect, $dateTime);
            if ($plantStatus->filter(static fn (Status $status) => $status->getName() === EquipmentStatusEnum::PLANT_THIRSTY)->isEmpty()) {
                $this->addFruit($gameEquipment, $plantType, $dateTime);
            }
        }

        $this->handleStatus($gameEquipment, $dateTime);
    }

    private function handleStatus(GameEquipment $gamePlant, \DateTime $dateTime): void
    {
        // If plant was thirsty, become dried
        if ($gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY) !== null) {
            $this->statusService->removeStatus(EquipmentStatusEnum::PLANT_THIRSTY, $gamePlant, [EventEnum::NEW_CYCLE], new \DateTime());
            $this->statusService->createStatusFromName(EquipmentStatusEnum::PLANT_DRY, $gamePlant, [EventEnum::NEW_CYCLE], new \DateTime());

        // If plant was dried, become hydropot
        } elseif ($gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_DRY) !== null) {
            $this->handleDriedPlant($gamePlant, $dateTime);

        // If plant was not thirsty or dried become thirsty
        } else {
            $this->statusService->createStatusFromName(EquipmentStatusEnum::PLANT_THIRSTY, $gamePlant, [EventEnum::NEW_CYCLE], new \DateTime());
        }
    }

    private function handleDriedPlant(GameEquipment $gamePlant, \DateTime $dateTime): void
    {
        $holder = $gamePlant->getHolder();

        // Create a new hydropot
        $equipmentEvent = new InteractWithEquipmentEvent(
            $gamePlant,
            null,
            VisibilityEnum::PUBLIC,
            [PlantLogEnum::PLANT_DEATH],
            $dateTime
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::HYDROPOT,
            $holder,
            [PlantLogEnum::PLANT_DEATH],
            $dateTime,
            VisibilityEnum::HIDDEN
        );
    }

    private function addFruit(GameEquipment $gamePlant, Plant $plantType, \DateTime $dateTime): void
    {
        /** @var ArrayCollection<int, GameEquipment> $producedFruits */
        $producedFruits = new ArrayCollection();

        $fruit = $this->gameEquipmentService->createGameEquipmentFromName(
            $plantType->getFruitName(),
            $gamePlant->getPlace(), // If plant is not in a room, it is in player inventory
            [EventEnum::PLANT_PRODUCTION],
            $dateTime,
            VisibilityEnum::PUBLIC
        );
        $producedFruits->add($fruit);

        $daedalus = $gamePlant->getDaedalus();
        $heatLamps = $daedalus->getProjectByName(ProjectName::HEAT_LAMP);

        $plantIsInGarden = $gamePlant->getPlace()->getName() === RoomEnum::HYDROPONIC_GARDEN;
        $heatLampsAreFinished = $heatLamps->isFinished();
        $heatLampsAreActivated = $this->randomService->isSuccessful($heatLamps->getActivationRate());

        if ($plantIsInGarden && $heatLampsAreFinished && $heatLampsAreActivated) {
            $fruit = $this->gameEquipmentService->createGameEquipmentFromName(
                $plantType->getFruitName(),
                $gamePlant->getPlace(),
                [EventEnum::PLANT_PRODUCTION],
                $dateTime,
                VisibilityEnum::PUBLIC
            );
            $producedFruits->add($fruit);
        }

        if ($daedalus->hasFinishedProject(ProjectName::FOOD_RETAILER) && $plantIsInGarden) {
            foreach ($producedFruits as $producedFruit) {
                $this->gameEquipmentService->moveEquipmentTo(
                    equipment: $producedFruit,
                    newHolder: $daedalus->getPlaceByNameOrThrow(RoomEnum::REFECTORY),
                    visibility: VisibilityEnum::PUBLIC,
                    tags: [ProjectName::FOOD_RETAILER->value],
                    time: $dateTime,
                );
            }
        }
    }

    private function addOxygen(GameEquipment $gamePlant, PlantEffect $plantEffect, \DateTime $date): void
    {
        // Add Oxygen
        if ($oxygen = $plantEffect->getOxygen()) {
            $daedalusEvent = new DaedalusVariableEvent(
                $gamePlant->getDaedalus(),
                DaedalusVariableEnum::OXYGEN,
                $oxygen,
                [EventEnum::PLANT_PRODUCTION],
                $date
            );
            $this->eventService->callEvent($daedalusEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }
}
