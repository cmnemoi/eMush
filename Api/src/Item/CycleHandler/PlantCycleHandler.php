<?php

namespace Mush\Item\CycleHandler;

use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\Plant;
use Mush\Item\Enum\ItemEnum;
use Mush\Item\Enum\PlantStatusEnum;
use Mush\Item\Service\FruitServiceInterface;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\RoomLog\Enum\PlantLogEnum;

class PlantCycleHandler implements CycleHandlerInterface
{
    private GameItemServiceInterface $itemService;
    private RandomServiceInterface $randomService;
    private FruitServiceInterface $fruitService;
    private RoomLogServiceInterface $roomLogService;
    private GameConfig $gameConfig;

    const DISEASE_PERCENTAGE = 3;

    public function __construct(
        GameItemServiceInterface $itemService,
        RandomServiceInterface $randomService,
        FruitServiceInterface $fruitService,
        RoomLogServiceInterface $roomLogService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->itemService = $itemService;
        $this->randomService = $randomService;
        $this->fruitService = $fruitService;
        $this->roomLogService = $roomLogService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function handleNewCycle($plant, \DateTime $dateTime)
    {
        if (!$plant instanceof Plant) {
            return;
        }

        if ($this->randomService->random(1, 100) <= self::DISEASE_PERCENTAGE) {
            $plant->addStatus(PlantStatusEnum::DISEASED);
        }

        if ($plant->getCharge() < $plant->getGamePlant()->getMaturationTime()) {
            $plant->setCharge($plant->getCharge() + 1);

            //If plant is mature
            if ($plant->isMature() && $plant->hasStatus(PlantStatusEnum::YOUNG)) {
                $plant->removeStatus(PlantStatusEnum::YOUNG);
                $this->roomLogService->createItemLog(
                    PlantLogEnum::PLANT_MATURITY,
                    $plant->getRoom() ?? $plant->getPlayer()->getRoom(),
                    $plant,
                    VisibilityEnum::PUBLIC,
                    $dateTime
                );
            }
        }

        $this->itemService->persist($plant);
    }

    public function handleNewDay($plant, \DateTime $dateTime)
    {
        if (!$plant instanceof Plant) {
            return;
        }

        $this->addOxygen($plant);
        $this->addFruit($plant, $dateTime);
        $this->handleStatus($plant, $dateTime);

        $this->itemService->persist($plant);
    }


    private function handleStatus(Plant $plant, \DateTime $dateTime)
    {
        if ($plant->hasStatus(PlantStatusEnum::THIRSTY)) { // If plant was thirsty, become dried
            $plant->removeStatus(PlantStatusEnum::THIRSTY);
            $plant->addStatus(PlantStatusEnum::DRIED);
        } elseif ($plant->hasStatus(PlantStatusEnum::DRIED)) {  // If plant was dried, become hydropot
            $this->handleDriedPlant($plant, $dateTime);
        } else {  // If plant was not thirsty or dried become thirsty
            $plant->addStatus(PlantStatusEnum::THIRSTY);
        }
    }

    private function handleDriedPlant(Plant $plant, \DateTime $dateTime)
    {
        if ($plant->hasStatus(PlantStatusEnum::DRIED)) {
            // Create a new hydropot
            $hydropot = $this->itemService->createItem(ItemEnum::HYDROPOT);

            // If plant is not in a room, it is in player inventory
            $place = $plant->getRoom() ? $plant->getRoom() : $plant->getPlayer();
            $room = $place;
            if ($place instanceof Player) {
                $plant->setPlayer(null);
                $hydropot->setPlayer($place);
                $room = $place->getRoom();
            } else {
                $plant->setRoom(null);
                $hydropot->setRoom($place);
            }
            $this->roomLogService->createItemLog(
                PlantLogEnum::PLANT_DEATH,
                $room,
                $plant,
                VisibilityEnum::PUBLIC,
                $dateTime
            );
            $this->itemService->delete($plant);     // Remove plant
            $this->itemService->persist($hydropot); // Add hydropot
        }
    }

    private function addFruit(Plant $plant, \DateTime $dateTime)
    {
        //If plant is young, thirsty, dried or diseased, do not produce fruit
        if (array_intersect(
            $plant->getStatuses(),
            [
                PlantStatusEnum::THIRSTY,
                PlantStatusEnum::DRIED,
                PlantStatusEnum::DISEASED,
                PlantStatusEnum::YOUNG
            ]
        )
        ) {
            return;
        }
        // If plant is not in a room, it is in player inventory
        $place = $plant->getRoom() ? $plant->getRoom() : $plant->getPlayer();
        $room = $place;

        // Create a new fruit
        $fruit = $this->fruitService->createFruit($plant->getGamePlant()->getGameFruit());
        if ($place instanceof Player) {
            $room = $place->getRoom();
            if ($place->getItems() < $this->gameConfig->getMaxItemInInventory()) {
                $fruit->setPlayer($place);
            } else {
                $fruit->setRoom($place->getRoom());
            }
        } else {
            $fruit->setRoom($place);
        }

        $this->roomLogService->createItemLog(
            PlantLogEnum::PLANT_NEW_FRUIT,
            $room,
            $fruit,
            VisibilityEnum::PUBLIC,
            $dateTime
        );

        $this->itemService->persist($fruit);
    }

    private function addOxygen(Plant $plant)
    {
        //If plant is young, dried or diseased, do not produce oxygen
        if (array_intersect(
            $plant->getStatuses(),
            [PlantStatusEnum::DRIED, PlantStatusEnum::DISEASED, PlantStatusEnum::YOUNG]
        )) {
            return;
        }
        // If plant is not in a room, it is in player inventory
        $place = $plant->getRoom() ? $plant->getRoom() : $plant->getPlayer();

        //Add Oxygen
        if (($oxygen = $plant->getGamePlant()->getOxygen())) {
            $daedalus = $place->getDaedalus();
            $daedalus->setOxygen($daedalus->getOxygen() + $oxygen);
        }
    }
}
