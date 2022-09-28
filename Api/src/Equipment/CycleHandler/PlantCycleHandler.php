<?php

namespace Mush\Equipment\CycleHandler;

use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
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
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\Validator\Exception\LogicException;

class PlantCycleHandler extends AbstractCycleHandler
{
    protected string $name = EquipmentMechanicEnum::PLANT;

    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;
    private EquipmentEffectServiceInterface $equipmentEffectService;

    public function __construct(
        EventServiceInterface $eventService,
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        EquipmentEffectServiceInterface $equipmentEffectService
    ) {
        $this->eventService = $eventService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->randomService = $randomService;
        $this->equipmentEffectService = $equipmentEffectService;
    }

    public function handleNewCycle($object, $daedalus, \DateTime $dateTime, array $context = []): void
    {
        /** @var GameItem $gamePlant */
        $gamePlant = $object;

        if (!$gamePlant instanceof GameEquipment) {
            return;
        }

        $plantType = $gamePlant->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PLANT);

        if (!$plantType instanceof Plant) {
            return;
        }

        $plantEffect = $this->equipmentEffectService->getPlantEffect($plantType, $daedalus);

        /** @var ChargeStatus $youngStatus */
        $youngStatus = $gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_YOUNG);
        if ($youngStatus &&
            $youngStatus->getCharge() >= $plantEffect->getMaturationTime()
        ) {
            $statusEvent = new StatusEvent(
                EquipmentStatusEnum::PLANT_YOUNG,
                $gamePlant,
                EventEnum::NEW_CYCLE,
                $dateTime
            );
            $statusEvent->setVisibility(VisibilityEnum::PUBLIC);

            $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_REMOVED, true);
        }

        $diseaseRate = $daedalus->getGameConfig()->getDifficultyConfig()->getPlantDiseaseRate();

        if ($this->randomService->isSuccessful($diseaseRate) &&
            !$gamePlant->hasStatus(EquipmentStatusEnum::PLANT_DISEASED)
        ) {
            $statusEvent = new StatusEvent(EquipmentStatusEnum::PLANT_DISEASED, $gamePlant, EventEnum::NEW_CYCLE, new \DateTime());

            $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
        }
    }

    public function handleNewDay($object, $daedalus, \DateTime $dateTime, array $context = []): void
    {
        /** @var GameItem $gamePlant */
        $gamePlant = $object;

        if (!$gamePlant instanceof GameEquipment) {
            return;
        }

        $plantType = $gamePlant->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PLANT);

        if (!$plantType instanceof Plant) {
            return;
        }

        $plantEffect = $this->equipmentEffectService->getPlantEffect($plantType, $daedalus);

        $plantStatus = $gamePlant->getStatuses();

        // If plant is young, dried or diseased, do not produce oxygen
        if ($plantStatus->filter(
            fn (Status $status) => in_array(
                $status->getName(),
                [
                    EquipmentStatusEnum::PLANT_DRY,
                    EquipmentStatusEnum::PLANT_DISEASED,
                    EquipmentStatusEnum::PLANT_YOUNG,
                ]
            )
        )->isEmpty()
        ) {
            $this->addOxygen($gamePlant, $plantEffect, $dateTime);
            if ($plantStatus->filter(fn (Status $status) => in_array(
                $status->getName(),
                [EquipmentStatusEnum::PLANT_THIRSTY]
            ))->isEmpty()
            ) {
                $this->addFruit($gamePlant, $plantType, $dateTime);
            }
        }

        $this->handleStatus($gamePlant, $dateTime);
    }

    private function handleStatus(GameItem $gamePlant, \DateTime $dateTime): void
    {
        // If plant was thirsty, become dried
        if (($thirsty = $gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY)) !== null) {
            $gamePlant->removeStatus($thirsty);
            $statusEvent = new StatusEvent(EquipmentStatusEnum::PLANT_DRY, $gamePlant, EventEnum::NEW_CYCLE, new \DateTime());

            $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
        // If plant was dried, become hydropot
        } else if ($gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_DRY) !== null) {
            $this->handleDriedPlant($gamePlant, $dateTime);
        // If plant was not thirsty or dried become thirsty
        } else {
            $statusEvent = new StatusEvent(EquipmentStatusEnum::PLANT_THIRSTY, $gamePlant, EventEnum::NEW_CYCLE, new \DateTime());

            $this->eventService->callEvent($statusEvent, StatusEvent::STATUS_APPLIED);
        }
    }

    private function handleDriedPlant(GameItem $gamePlant, \DateTime $dateTime): void
    {
        $place = $gamePlant->getPlace();
        $holder = $gamePlant->getHolder();

        if ($holder === null) {
            throw new LogicException('Equipment holder is empty');
        }

        // Create a new hydropot
        $equipmentEvent = new InteractWithEquipmentEvent(
            $gamePlant,
            $place,
            VisibilityEnum::PUBLIC,
            PlantLogEnum::PLANT_DEATH,
            $dateTime
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $hydropot = $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::HYDROPOT,
            $holder,
            PlantLogEnum::PLANT_DEATH,
            $dateTime
        );

        $equipmentEvent = new EquipmentEvent(
            $hydropot,
            true,
            VisibilityEnum::HIDDEN,
            PlantLogEnum::PLANT_DEATH,
            $dateTime
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);
    }

    private function addFruit(GameItem $gamePlant, Plant $plantType, \DateTime $dateTime): void
    {
        // If plant is young, thirsty, dried or diseased, do not produce fruit
        if (!$gamePlant->getStatuses()
            ->filter(
                fn (Status $status) => in_array(
                    $status->getName(),
                    [
                        EquipmentStatusEnum::PLANT_DRY,
                        EquipmentStatusEnum::PLANT_DISEASED,
                        EquipmentStatusEnum::PLANT_YOUNG,
                        EquipmentStatusEnum::PLANT_THIRSTY,
                    ]
                )
            )
            ->isEmpty()
        ) {
            return;
        }

        // If plant is not in a room, it is in player inventory
        $place = $gamePlant->getPlace();

        $fruit = $this->gameEquipmentService->createGameEquipment(
            $plantType->getFruit(),
            $place,
            EventEnum::PLANT_PRODUCTION,
            $dateTime
        );

        $equipmentEvent = new EquipmentEvent(
            $fruit,
            true,
            VisibilityEnum::PUBLIC,
            EventEnum::PLANT_PRODUCTION,
            $dateTime
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);
    }

    private function addOxygen(GameItem $gamePlant, PlantEffect $plantEffect, \DateTime $date): void
    {
        $daedalus = $gamePlant->getPlace()->getDaedalus();
        // Add Oxygen
        if ($oxygen = $plantEffect->getOxygen()) {
            $daedalusEvent = new DaedalusModifierEvent(
                $daedalus,
                DaedalusVariableEnum::OXYGEN,
                $oxygen,
                EventEnum::PLANT_PRODUCTION,
                $date
            );
            $this->eventService->callEvent($daedalusEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
        }
    }
}
