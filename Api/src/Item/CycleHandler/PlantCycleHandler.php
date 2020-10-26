<?php

namespace Mush\Item\CycleHandler;

use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\Fruit;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\GamePlant;
use Mush\Item\Entity\Plant;
use Mush\Item\Enum\ItemEnum;
use Mush\Item\Enum\PlantStatusEnum;
use Mush\Item\Service\GameFruitServiceInterface;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\RoomLog\Enum\PlantLogEnum;

class PlantCycleHandler implements CycleHandlerInterface
{
    private GameItemServiceInterface $itemService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private GameConfig $gameConfig;

    const DISEASE_PERCENTAGE = 3;

    public function __construct(
        GameItemServiceInterface $itemService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->itemService = $itemService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function handleNewCycle($gamePlant, \DateTime $dateTime)
    {
        if (!$gamePlant instanceof GameItem || !$gamePlant->getItem() instanceof Plant) {
            return;
        }
        /** @var Plant $plant */
        $plant = $gamePlant->getItem();

        if ($this->randomService->random(1, 100) <= self::DISEASE_PERCENTAGE) {
            $gamePlant->addStatus(PlantStatusEnum::DISEASED);
        }

        if ($gamePlant->getCharge() < $plant->getMaturationTime()) {
            $gamePlant->setCharge($gamePlant->getCharge() + 1);

            //If plant is mature
            if ($gamePlant->getCharge() >= $plant->getMaturationTime() && $gamePlant->hasStatus(PlantStatusEnum::YOUNG)) {
                $gamePlant->removeStatus(PlantStatusEnum::YOUNG);
                $this->roomLogService->createItemLog(
                    PlantLogEnum::PLANT_MATURITY,
                    $gamePlant->getRoom() ?? $gamePlant->getPlayer()->getRoom(),
                    $gamePlant,
                    VisibilityEnum::PUBLIC,
                    $dateTime
                );
            }
        }

        $this->itemService->persist($gamePlant);
    }

    public function handleNewDay($gamePlant, \DateTime $dateTime)
    {
        if (!$gamePlant instanceof GameItem || !$gamePlant->getItem() instanceof Plant) {
            return;
        }

        $this->addOxygen($gamePlant);
        $this->addFruit($gamePlant, $dateTime);
        $this->handleStatus($gamePlant, $dateTime);

        $this->itemService->persist($gamePlant);
    }


    private function handleStatus(GameItem $gamePlant, \DateTime $dateTime)
    {
        if ($gamePlant->hasStatus(PlantStatusEnum::THIRSTY)) { // If plant was thirsty, become dried
            $gamePlant->removeStatus(PlantStatusEnum::THIRSTY);
            $gamePlant->addStatus(PlantStatusEnum::DRIED);
        } elseif ($gamePlant->hasStatus(PlantStatusEnum::DRIED)) {  // If plant was dried, become hydropot
            $this->handleDriedPlant($gamePlant, $dateTime);
        } else {  // If plant was not thirsty or dried become thirsty
            $gamePlant->addStatus(PlantStatusEnum::THIRSTY);
        }
    }

    private function handleDriedPlant(GameItem $gamePlant, \DateTime $dateTime)
    {
        if ($gamePlant->hasStatus(PlantStatusEnum::DRIED)) {
            // If plant is not in a room, it is in player inventory
            $place = $gamePlant->getRoom() ? $gamePlant->getRoom() : $gamePlant->getPlayer();
            // Create a new hydropot
            $hydropot = $this->itemService->createGameItemFromName(ItemEnum::HYDROPOT, $place->getDaedalus());

            $room = $place;
            if ($place instanceof Player) {
                $gamePlant->setPlayer(null);
                $hydropot->setPlayer($place);
                $room = $place->getRoom();
            } else {
                $gamePlant->setRoom(null);
                $hydropot->setRoom($place);
            }
            $this->roomLogService->createItemLog(
                PlantLogEnum::PLANT_DEATH,
                $room,
                $gamePlant,
                VisibilityEnum::PUBLIC,
                $dateTime
            );
            $this->itemService->delete($gamePlant);     // Remove plant
            $this->itemService->persist($hydropot); // Add hydropot
        }
    }

    private function addFruit(GameItem $gamePlant, \DateTime $dateTime)
    {
        //If plant is young, thirsty, dried or diseased, do not produce fruit
        if (array_intersect(
            $gamePlant->getStatuses(),
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
        $place = $gamePlant->getRoom() ? $gamePlant->getRoom() : $gamePlant->getPlayer();
        $room = $place;

        /** @var Plant $plant */
        $plant = $gamePlant->getItem();
        // Create a new fruit
        $gameFruit = $plant->getFruit()->createGameItem();
        if ($place instanceof Player) {
            $room = $place->getRoom();
            if ($place->getItems() < $this->gameConfig->getMaxItemInInventory()) {
                $gameFruit->setPlayer($place);
            } else {
                $gameFruit->setRoom($place->getRoom());
            }
        } else {
            $gameFruit->setRoom($place);
        }

        $this->roomLogService->createItemLog(
            PlantLogEnum::PLANT_NEW_FRUIT,
            $room,
            $gameFruit,
            VisibilityEnum::PUBLIC,
            $dateTime
        );

        $this->itemService->persist($gameFruit);
    }

    private function addOxygen(GameItem $gamePlant)
    {
        //If plant is young, dried or diseased, do not produce oxygen
        if (array_intersect(
            $gamePlant->getStatuses(),
            [PlantStatusEnum::DRIED, PlantStatusEnum::DISEASED, PlantStatusEnum::YOUNG]
        )) {
            return;
        }
        // If plant is not in a room, it is in player inventory
        $place = $gamePlant->getRoom() ? $gamePlant->getRoom() : $gamePlant->getPlayer();

        //Add Oxygen
        if (($oxygen = $gamePlant->getItem()->getOxygen())) {
            $daedalus = $place->getDaedalus();
            $daedalus->setOxygen($daedalus->getOxygen() + $oxygen);
        }
    }
}
