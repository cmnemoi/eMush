<?php

namespace Mush\Equipment\CycleHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\PlantEffect;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class PlantCycleHandler extends AbstractCycleHandler
{
    protected string $name = EquipmentMechanicEnum::PLANT;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private GameConfig $gameConfig;
    private StatusServiceInterface $statusService;
    private EquipmentEffectServiceInterface $equipmentEffectService;

    private const DISEASE_PERCENTAGE = 3;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        GameConfigServiceInterface $gameConfigService,
        StatusServiceInterface $statusService,
        EquipmentEffectServiceInterface $equipmentEffectService
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->equipmentEffectService = $equipmentEffectService;
        $this->statusService = $statusService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function handleNewCycle($gamePlant, $daedalus, \DateTime $dateTime)
    {
        if (!$gamePlant instanceof GameEquipment) {
            return;
        }
        $plantType = $gamePlant->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PLANT);
        if (null === $gamePlant || !$plantType instanceof Plant) {
            return;
        }

        if ($this->randomService->randomPercent() <= self::DISEASE_PERCENTAGE) {
            $diseased = new Status();
            $diseased
                ->setName(EquipmentStatusEnum::PLANT_DISEASED)
                ->setVisibility(VisibilityEnum::PUBLIC)
                ->setGameEquipment($gamePlant)
            ;
        }

        $plantEffect = $this->equipmentEffectService->getPlantEffect($plantType, $daedalus);

        if (
            ($youngStatus = $gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_YOUNG)) &&
            $youngStatus->getCharge() >= $plantEffect->getMaturationTime()
        ) {
            $gamePlant->removeStatus($youngStatus);
            $this->roomLogService->createEquipmentLog(
                PlantLogEnum::PLANT_MATURITY,
                $gamePlant->getRoom() ?? $gamePlant->getPlayer()->getRoom(),
                null,
                $gamePlant,
                VisibilityEnum::PUBLIC,
                $dateTime
            );
        }

        $this->gameEquipmentService->persist($gamePlant);
    }

    public function handleNewDay($gamePlant, $daedalus, \DateTime $dateTime)
    {
        if (!$gamePlant instanceof GameEquipment) {
            return;
        }
        $plantType = $gamePlant->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PLANT);
        if (null === $gamePlant || !$plantType instanceof Plant) {
            return;
        }

        $plantEffect = $this->equipmentEffectService->getPlantEffect($plantType, $daedalus);

        $plantStatus = $gamePlant->getStatuses();

        //If plant is young, dried or diseased, do not produce oxygen
        if (
            $plantStatus->filter(
                fn (Status $status) => in_array(
                    $status->getName(),
                    [EquipmentStatusEnum::PLANT_DRIED_OUT, EquipmentStatusEnum::PLANT_DISEASED, EquipmentStatusEnum::PLANT_YOUNG]
                )
            )->isEmpty()
        ) {
            $this->addOxygen($gamePlant, $plantEffect);
            if (
                $plantStatus->filter(fn (Status $status) => in_array(
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

    private function handleStatus(GameEquipment $gamePlant, \DateTime $dateTime)
    {
        // If plant was thirsty, become dried
        if (($thirsty = $gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_THIRSTY)) !== null) {
            $gamePlant->removeStatus($thirsty);
            $driedStatus = $this->statusService->createCoreEquipmentStatus(EquipmentStatusEnum::PLANT_DRIED_OUT, $gamePlant);
            $gamePlant->addStatus($driedStatus);
        // If plant was dried, become hydropot
        } elseif ($gamePlant->getStatusByName(EquipmentStatusEnum::PLANT_DRIED_OUT) !== null) {
            $this->handleDriedPlant($gamePlant, $dateTime);
        // If plant was not thirsty or dried become thirsty
        } else {
            $thirstyStatus = $this->statusService->createCoreEquipmentStatus(EquipmentStatusEnum::PLANT_THIRSTY, $gamePlant);
            $gamePlant->addStatus($thirstyStatus);
        }
    }

    private function handleDriedPlant(GameEquipment $gamePlant, \DateTime $dateTime)
    {
        // If plant is not in a room, it is in player inventory
        $place = $gamePlant->getRoom() ? $gamePlant->getRoom() : $gamePlant->getPlayer();
        // Create a new hydropot
        $hydropot = $this->gameEquipmentService->createGameEquipmentFromName(ItemEnum::HYDROPOT, $place->getDaedalus());

        $room = $place;
        if ($place instanceof Player) {
            $gamePlant->setPlayer(null);
            $hydropot->setPlayer($place);
            $room = $place->getRoom();
        } else {
            $gamePlant->setRoom(null);
            $hydropot->setRoom($place);
        }
        $this->roomLogService->createEquipmentLog(
            PlantLogEnum::PLANT_DEATH,
            $room,
            null,
            $gamePlant,
            VisibilityEnum::PUBLIC,
            $dateTime
        );

        $gamePlant->removeLocation();
        $this->gameEquipmentService->delete($gamePlant); // Remove plant
        $this->gameEquipmentService->persist($hydropot); // Add hydropot
    }

    private function addFruit(GameEquipment $gamePlant, Plant $plantType, \DateTime $dateTime)
    {
        //If plant is young, thirsty, dried or diseased, do not produce fruit
        if (
            !$gamePlant->getStatuses()
            ->filter(
                fn (Status $status) => in_array(
                    $status->getName(),
                    [
                        EquipmentStatusEnum::PLANT_DRIED_OUT,
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
        $place = $gamePlant->getRoom() ?? $gamePlant->getPlayer();

        // Create a new fruit
        $gameFruit = $this->gameEquipmentService->createGameEquipment($plantType->getFruit(), $place->getDaedalus());

        if ($place instanceof Player) {
            $room = $place->getRoom();
            if ($place->getItems() < $this->gameConfig->getMaxItemInInventory()) {
                $gameFruit->setPlayer($place);
            } else {
                $gameFruit->setRoom($place->getRoom());
            }
        } else {
            $room = $place;
            $gameFruit->setRoom($place);
        }

        $this->gameEquipmentService->persist($gameFruit);

        $this->roomLogService->createEquipmentLog(
            PlantLogEnum::PLANT_NEW_FRUIT,
            $room,
            null,
            $gameFruit,
            VisibilityEnum::PUBLIC,
            $dateTime
        );
    }

    private function addOxygen(GameEquipment $gamePlant, PlantEffect $plantEffect)
    {
        // If plant is not in a room, it is in player inventory
        $place = $gamePlant->getRoom() ? $gamePlant->getRoom() : $gamePlant->getPlayer();

        //Add Oxygen
        if (($oxygen = $plantEffect->getOxygen())) {
            $daedalus = $place->getDaedalus();
            $daedalus->setOxygen($daedalus->getOxygen() + $oxygen);
        }
    }
}
