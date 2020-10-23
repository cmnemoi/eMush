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
use Mush\Item\Service\ItemServiceInterface;
use Mush\Player\Entity\Player;

class PlantCycleHandler implements CycleHandlerInterface
{
    private ItemServiceInterface $itemService;
    private RandomServiceInterface $randomService;
    private FruitServiceInterface $fruitService;
    private GameConfig $gameConfig;

    CONST DISEASE_PERCENTAGE = 3;

    public function __construct(
        ItemServiceInterface $itemService,
        RandomServiceInterface $randomService,
        FruitServiceInterface $fruitService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->itemService = $itemService;
        $this->randomService = $randomService;
        $this->fruitService = $fruitService;
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

        if ($plant->getLoad() < $plant->getGamePlant()->getMaturationTime()) {
            $plant->setLoad($plant->getLoad() + 1);

            //If plant is mature
            if ($plant->isMature()) {
                $plant->removeStatus(PlantStatusEnum::YOUNG);
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
        $this->addFruit($plant);
        $this->handleStatus($plant);

        $this->itemService->persist($plant);
    }


    private function handleStatus(Plant $plant)
    {
        if ($plant->hasStatus(PlantStatusEnum::THIRSTY)) { // If plant was thirsty, become dried
            $plant->removeStatus(PlantStatusEnum::THIRSTY);
            $plant->addStatus(PlantStatusEnum::DRIED);
        } elseif ($plant->hasStatus(PlantStatusEnum::DRIED)) {  // If plant was dried, become hydropot
            $this->handleDriedPlant($plant);
        } else {  // If plant was not thirsty or dried become thirsty
            $plant->addStatus(PlantStatusEnum::THIRSTY);
        }

    }

    private function handleDriedPlant(Plant $plant)
    {
        if ($plant->hasStatus(PlantStatusEnum::DRIED)) {
            // Create a new hydropot
            $hydropot = $this->itemService->createItem(ItemEnum::HYDROPOT);

            // If plant is not in a room, it is in player inventory
            $place = $plant->getRoom() ? $plant->getRoom() : $plant->getPlayer();
            if ($place instanceof Player) {
                $plant->setPlayer(null);
                $hydropot->setPlayer($place);
            } else {
                $plant->setRoom(null);
                $hydropot->setRoom($place);
            }

            $this->itemService->delete($plant);     // Remove plant
            $this->itemService->persist($hydropot); // Add hydropot
        }
    }

    private function addFruit(Plant $plant)
    {
        //If plant is young, thirsty, dried or diseased, do not produce fruit
        if (array_intersect(
            $plant->getStatuses(),
            [PlantStatusEnum::THIRSTY, PlantStatusEnum::DRIED, PlantStatusEnum::DISEASED, PlantStatusEnum::YOUNG])
        ) {
            return;
        }
        // If plant is not in a room, it is in player inventory
        $place = $plant->getRoom() ? $plant->getRoom() : $plant->getPlayer();

        // Create a new fruit
        $fruit = $this->fruitService->createFruit($plant->getGamePlant()->getGameFruit());
        if ($place instanceof Player) {
            if ($place->getItems() < $this->gameConfig->getMaxItemInInventory()) {
                $fruit->setPlayer($place);
            } else {
                $fruit->setRoom($place->getRoom());
            }
        } else {
            $fruit->setRoom($place);
        }
        $this->itemService->persist($fruit);
    }

    private function addOxygen(Plant $plant)
    {
        //If plant is young, dried or diseased, do not produce oxygen
        if (array_intersect($plant->getStatuses(), [PlantStatusEnum::DRIED, PlantStatusEnum::DISEASED, PlantStatusEnum::YOUNG])) {
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