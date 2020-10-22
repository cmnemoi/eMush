<?php

namespace Mush\Item\CycleHandler;

use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\Plant;
use Mush\Item\Enum\PlantStatusEnum;
use Mush\Item\Service\FruitServiceInterface;
use Mush\Item\Service\ItemServiceInterface;
use Mush\Player\Entity\Player;

class PlantCycleHandler implements CycleHandlerInterface
{
    private ItemServiceInterface $itemService;
    private FruitServiceInterface $fruitService;
    private GameConfig $gameConfig;

    public function __construct(ItemServiceInterface $itemService, FruitServiceInterface $fruitService, GameConfigServiceInterface $gameConfigService)
    {
        $this->itemService = $itemService;
        $this->fruitService = $fruitService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function handleNewCycle($plant, \DateTime $dateTime)
    {
        if (!$plant instanceof Plant) {
            return;
        }

        if ($plant->getLoad() < $plant->getGamePlant()->getMaturationTime()) {
            $plant->setLoad($plant->getLoad() + 1);

            //If plant is mature
            if ($plant->isMature()) {
                $plant->removeStatus(PlantStatusEnum::YOUNG);
            }
        }
    }

    public function handleNewDay($plant, \DateTime $dateTime)
    {
        if (!$plant instanceof Plant) {
            return;
        }

        if ($plant->isMature() &&
            !($plant->hasStatus(PlantStatusEnum::DISEASED) || $plant->hasStatus(PlantStatusEnum::DRIED))
        ) {
            // If plant is not in a room, it is in player inventory
            $place = $plant->getRoom() ? $plant->getRoom() : $plant->getPlayer();

            //Add Oxygen
            if (($oxygen = $plant->getGamePlant()->getOxygen())) {
                $daedalus = $place->getDaedalus();
                $daedalus->setOxygen($daedalus->getOxygen() + $oxygen);
            }

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

        if ($plant->hasStatus(PlantStatusEnum::THIRSTY)) { // If plant was thirsty, become dried
            $plant->removeStatus(PlantStatusEnum::THIRSTY);
            $plant->addStatus(PlantStatusEnum::DRIED);
        } elseif ($plant->hasStatus(PlantStatusEnum::DRIED)) {  // If plant was dried, become deceased
            $plant->removeStatus(PlantStatusEnum::DRIED);
            $plant->addStatus(PlantStatusEnum::DISEASED);
        } elseif (!$plant->hasStatus(PlantStatusEnum::DISEASED)) {  // If plant was not thirsty, dried or deceased become thirsty
            $plant->addStatus(PlantStatusEnum::THIRSTY);
        }

        $this->itemService->persist($plant);
    }
}