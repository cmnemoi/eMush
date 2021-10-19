<?php

namespace Mush\Equipment\CycleHandler;

use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Exception\LogicException;

class PlantCycleHandler extends AbstractCycleHandler
{
    protected string $name = EquipmentMechanicEnum::PLANT;

    private EventDispatcherInterface $eventDispatcher;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;
    private EquipmentEffectServiceInterface $equipmentEffectService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        EquipmentEffectServiceInterface $equipmentEffectService
    ) {
        $this->eventDispatcher = $eventDispatcher;
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

            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_REMOVED);
        }

        $diseaseRate = $daedalus->getGameConfig()->getDifficultyConfig()->getPlantDiseaseRate();

        if ($this->randomService->isSuccessful($diseaseRate) &&
            !$gamePlant->hasStatus(EquipmentStatusEnum::PLANT_DISEASED)
        ) {
            $statusEvent = new StatusEvent(EquipmentStatusEnum::PLANT_DISEASED, $gamePlant, EventEnum::NEW_CYCLE, new \DateTime());

            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
        }

        $this->gameEquipmentService->persist($gamePlant);
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

        //If plant is young, dried or diseased, do not produce oxygen
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

        $this->gameEquipmentService->persist($gamePlant);
    }

    private function handleStatus(GameItem $gamePlant, \DateTime $dateTime): void
    {
        // If plant was thirsty, become dried
        if (($thirsty = $gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY)) !== null) {
            $gamePlant->removeStatus($thirsty);
            $statusEvent = new StatusEvent(EquipmentStatusEnum::PLANT_DRY, $gamePlant, EventEnum::NEW_CYCLE, new \DateTime());

            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
        // If plant was dried, become hydropot
        } elseif ($gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_DRY) !== null) {
            $this->handleDriedPlant($gamePlant, $dateTime);
        // If plant was not thirsty or dried become thirsty
        } else {
            $statusEvent = new StatusEvent(EquipmentStatusEnum::PLANT_THIRSTY, $gamePlant, EventEnum::NEW_CYCLE, new \DateTime());

            $this->eventDispatcher->dispatch($statusEvent, StatusEvent::STATUS_APPLIED);
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
        /** @var GameItem $hydropot */
        $hydropot = $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::HYDROPOT,
            $holder,
            PlantLogEnum::PLANT_DEATH,
            $dateTime
        );

        $equipmentEvent = new EquipmentEvent(
            $gamePlant,
            $place,
            VisibilityEnum::PUBLIC,
            PlantLogEnum::PLANT_DEATH,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $equipmentEvent = new EquipmentEvent(
            $hydropot,
            $place,
            VisibilityEnum::HIDDEN,
            PlantLogEnum::PLANT_DEATH,
            new \DateTime()
        );
        if ($holder instanceof Player) {
            $equipmentEvent->setPlayer($holder);
        }

        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);
    }

    private function addFruit(GameItem $gamePlant, Plant $plantType, \DateTime $dateTime): void
    {
        //If plant is young, thirsty, dried or diseased, do not produce fruit
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

        /** @var GameItem $gameFruit */
        $gameFruit = $this->gameEquipmentService->createGameEquipment(
            $plantType->getFruit(),
            $place,
            EventEnum::PLANT_PRODUCTION,
            new \DateTime()
        );

        $equipmentEvent = new EquipmentEvent(
            $gameFruit,
            $place,
            VisibilityEnum::PUBLIC,
            EventEnum::PLANT_PRODUCTION,
            new \DateTime()
        );

        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);
    }

    private function addOxygen(GameItem $gamePlant, PlantEffect $plantEffect, \DateTime $date): void
    {
        $daedalus = $gamePlant->getPlace()->getDaedalus();
        //Add Oxygen
        if (($oxygen = $plantEffect->getOxygen())) {
            $daedalusEvent = new DaedalusModifierEvent(
                $daedalus,
                $oxygen,
                EventEnum::PLANT_PRODUCTION,
                $date
            );
            $this->eventDispatcher->dispatch($daedalusEvent, DaedalusModifierEvent::CHANGE_OXYGEN);
        }
    }
}
