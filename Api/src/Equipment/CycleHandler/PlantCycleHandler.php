<?php

declare(strict_types=1);

namespace Mush\Equipment\CycleHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\HolidayEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class PlantCycleHandler extends AbstractCycleHandler
{
    private const HALLOWEEN_JUMPKIN_SPAWN_RATE = 20;

    protected string $name = EquipmentMechanicEnum::PLANT;

    public function __construct(
        private EventServiceInterface $eventService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private RandomServiceInterface $randomService,
        private EquipmentEffectServiceInterface $equipmentEffectService,
        private StatusServiceInterface $statusService
    ) {}

    public function handleNewCycle(GameEquipment $gameEquipment, \DateTime $dateTime): void
    {
        $daedalus = $gameEquipment->getDaedalus();
        if (!$gameEquipment->hasMechanicByName(EquipmentMechanicEnum::PLANT)) {
            return;
        }

        $diseaseRate = $daedalus->getGameConfig()->getDifficultyConfig()->getPlantDiseaseRate();

        if ($this->randomService->isSuccessful($diseaseRate)) {
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
        if (!$gameEquipment->hasMechanicByName(EquipmentMechanicEnum::PLANT)) {
            return;
        }

        if ($gameEquipment->canProduceOxygen()) {
            $this->producePlantOxygen($gameEquipment, $dateTime);
        }

        if ($gameEquipment->canProduceFruit()) {
            $producedFruits = $this->producePlantFruits($gameEquipment, $dateTime);
            if ($this->shouldBeTransportedByFoodRetailer($gameEquipment)) {
                $this->moveFruitsToRefectory($producedFruits, $dateTime);
            }
        }

        $this->updatePlantHydrationStatus($gameEquipment, $dateTime);
    }

    private function shouldBeTransportedByFoodRetailer(GameEquipment $gamePlant): bool
    {
        return $gamePlant->getDaedalus()->hasFinishedProject(ProjectName::FOOD_RETAILER)
            && $gamePlant->isIn(RoomEnum::HYDROPONIC_GARDEN);
    }

    private function updatePlantHydrationStatus(GameEquipment $gamePlant, \DateTime $dateTime): void
    {
        if ($gamePlant->hasStatus(EquipmentStatusEnum::PLANT_DRY)) {
            $this->killDriedOutPlant($gamePlant, $dateTime);

            return;
        }

        if ($gamePlant->hasStatus(EquipmentStatusEnum::PLANT_THIRSTY)) {
            $this->makePlantDriedOut($gamePlant, $dateTime);

            return;
        }

        $this->makePlantThirsty($gamePlant, $dateTime);
    }

    private function makePlantDriedOut(GameEquipment $gamePlant, \DateTime $dateTime): void
    {
        $this->statusService->removeStatus(EquipmentStatusEnum::PLANT_THIRSTY, $gamePlant, [EventEnum::NEW_CYCLE], $dateTime);
        $this->statusService->createStatusFromName(EquipmentStatusEnum::PLANT_DRY, $gamePlant, [EventEnum::NEW_CYCLE], $dateTime);
    }

    private function killDriedOutPlant(GameEquipment $gamePlant, \DateTime $dateTime): void
    {
        $holder = $gamePlant->getHolder();

        // Destroy the plant
        $equipmentEvent = new InteractWithEquipmentEvent(
            $gamePlant,
            null,
            VisibilityEnum::PUBLIC,
            [PlantLogEnum::PLANT_DEATH],
            $dateTime
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        // Create a hydropot
        $this->gameEquipmentService->createGameEquipmentFromName(
            ItemEnum::HYDROPOT,
            $holder,
            [PlantLogEnum::PLANT_DEATH],
            $dateTime,
            VisibilityEnum::HIDDEN
        );
    }

    private function makePlantThirsty(GameEquipment $gamePlant, \DateTime $dateTime): void
    {
        $this->statusService->createStatusFromName(EquipmentStatusEnum::PLANT_THIRSTY, $gamePlant, [EventEnum::NEW_CYCLE], $dateTime);
    }

    private function producePlantFruits(GameEquipment $gamePlant, \DateTime $dateTime): ArrayCollection
    {
        $producedFruits = $this->createFruitsFromPlant($gamePlant, $dateTime);
        $halloweenJumpkins = $this->tryToCreateHalloweenJumpkin($gamePlant, $dateTime);

        return new ArrayCollection(array_merge($producedFruits->toArray(), $halloweenJumpkins->toArray()));
    }

    /**
     * @return ArrayCollection<int, GameEquipment>
     */
    private function createFruitsFromPlant(GameEquipment $gamePlant, \DateTime $dateTime): ArrayCollection
    {
        /** @var ArrayCollection<int, GameEquipment> $producedFruits */
        $producedFruits = new ArrayCollection();

        $this->createFruit($gamePlant, $dateTime, $producedFruits);
        if ($this->shouldProduceExtraFruit($gamePlant)) {
            $this->createFruit($gamePlant, $dateTime, $producedFruits);
        }

        return $producedFruits;
    }

    /**
     * @param ArrayCollection<int, GameEquipment> $producedFruits
     */
    private function createFruit(GameEquipment $gamePlant, \DateTime $dateTime, ArrayCollection $producedFruits): void
    {
        /** @var Plant $plantMechanic */
        $plantMechanic = $gamePlant->getEquipment()->getMechanicByNameOrThrow(EquipmentMechanicEnum::PLANT);

        $fruit = $this->gameEquipmentService->createGameEquipmentFromName(
            $plantMechanic->getFruitName(),
            $gamePlant->getPlace(),
            [EventEnum::PLANT_PRODUCTION],
            $dateTime,
            VisibilityEnum::PUBLIC
        );
        $producedFruits->add($fruit);
    }

    private function shouldProduceExtraFruit(GameEquipment $gamePlant): bool
    {
        $daedalus = $gamePlant->getDaedalus();
        $heatLamps = $daedalus->getProjectByName(ProjectName::HEAT_LAMP);
        $isPlantInGarden = $gamePlant->getPlace()->getName() === RoomEnum::HYDROPONIC_GARDEN;

        return $isPlantInGarden && $heatLamps->isFinished() && $this->randomService->isSuccessful($heatLamps->getActivationRate());
    }

    /**
     * @param ArrayCollection<int, GameEquipment> $producedFruits
     */
    private function moveFruitsToRefectory(ArrayCollection $producedFruits, \DateTime $dateTime): void
    {
        /** @var GameEquipment $fruit */
        foreach ($producedFruits as $fruit) {
            $this->gameEquipmentService->moveEquipmentTo(
                equipment: $fruit,
                newHolder: $fruit->getDaedalus()->getPlaceByNameOrThrow(RoomEnum::REFECTORY),
                visibility: VisibilityEnum::PUBLIC,
                tags: [ProjectName::FOOD_RETAILER->value],
                time: $dateTime,
            );
        }
    }

    private function producePlantOxygen(GameEquipment $gamePlant, \DateTime $date): void
    {
        /** @var Plant $plantMechanic */
        $plantMechanic = $gamePlant->getEquipment()->getMechanicByNameOrThrow(EquipmentMechanicEnum::PLANT);
        $plantEffect = $this->equipmentEffectService->getPlantEffect($plantMechanic, $gamePlant->getDaedalus());

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

    private function createJumpkinFruit(GameEquipment $gamePlant, \DateTime $dateTime): ArrayCollection
    {
        /** @var ArrayCollection<int, GameEquipment> $producedFruits */
        $producedFruits = new ArrayCollection();

        $fruit = $this->gameEquipmentService->createGameEquipmentFromName(
            GameFruitEnum::JUMPKIN,
            $gamePlant->getPlace(),
            [EventEnum::PLANT_PRODUCTION],
            $dateTime,
            VisibilityEnum::PUBLIC
        );
        $producedFruits->add($fruit);

        return $producedFruits;
    }

    private function tryToCreateHalloweenJumpkin(GameEquipment $gamePlant, \DateTime $dateTime): ArrayCollection
    {
        if (!$this->isHalloweenEvent($gamePlant)) {
            return new ArrayCollection();
        }

        if (!$this->isBiasedRollSuccessful(successRate: self::HALLOWEEN_JUMPKIN_SPAWN_RATE)) {
            return new ArrayCollection();
        }

        return $this->createJumpkinFruit($gamePlant, $dateTime);
    }

    private function isHalloweenEvent(GameEquipment $gamePlant): bool
    {
        return $gamePlant->getDaedalus()->getDaedalusConfig()->getHoliday() === HolidayEnum::HALLOWEEN;
    }

    private function isBiasedRollSuccessful(int $successRate): bool
    {
        return $this->randomService->rollTwiceAndAverage(1, 100) >= $successRate;
    }
}
