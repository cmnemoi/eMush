<?php

namespace Mush\Item\CycleHandler;

use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\PlantEffect;
use Mush\Item\Enum\ItemEnum;
use Mush\Item\Enum\PlantStatusEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Item\Service\ItemEffectServiceInterface;
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
    private ItemEffectServiceInterface $itemEffectService;

    const DISEASE_PERCENTAGE = 3;

    public function __construct(
        GameItemServiceInterface $itemService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        GameConfigServiceInterface $gameConfigService,
        ItemEffectServiceInterface $itemEffectService
    ) {
        $this->itemService = $itemService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->itemEffectService = $itemEffectService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function handleNewCycle($gamePlant, $daedalus, \DateTime $dateTime)
    {
        if (!$gamePlant instanceof GameItem) {
            return;
        }
        $plantType = $gamePlant->getItem()->getItemType(ItemEnum::PLANT);
        if ($gamePlant === null || !$plantType instanceof Plant) {
            return;
        }

        /** @var Plant $plant */
        $plant = $gamePlant->getItem();

        if ($this->randomService->random(1, 100) <= self::DISEASE_PERCENTAGE) {
            $gamePlant->addStatus(PlantStatusEnum::DISEASED);
        }

        $plantEffect = $this->itemEffectService->getPlantEffect($plantType, $daedalus);

        if ($gamePlant->getCharge() < $plantEffect->getMaturationTime()) {
            $gamePlant->setCharge($gamePlant->getCharge() + 1);

            //If plant is mature
            if ($gamePlant->getCharge() >= $plantEffect->getMaturationTime() &&
                $gamePlant->hasStatus(PlantStatusEnum::YOUNG)
            ) {
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

    public function handleNewDay($gamePlant, $daedalus, \DateTime $dateTime)
    {
        if (!$gamePlant instanceof GameItem) {
            return;
        }
        $plantType = $gamePlant->getItem()->getItemType(ItemEnum::PLANT);
        if ($gamePlant === null || !$plantType instanceof Plant) {
            return;
        }

        $plantEffect = $this->itemEffectService->getPlantEffect($plantType, $daedalus);

        $this->addOxygen($gamePlant, $plantEffect);
        $this->addFruit($gamePlant, $plantType, $dateTime);
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
            $this->itemService->delete($gamePlant); // Remove plant
            $this->itemService->persist($hydropot); // Add hydropot
        }
    }

    private function addFruit(GameItem $gamePlant, Plant $plantType, \DateTime $dateTime)
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
        $place = $gamePlant->getRoom() ?? $gamePlant->getPlayer();
        $room = $place;

        // Create a new fruit
        $gameFruit = $plantType->getFruit()->createGameItem();
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

        $this->itemService->persist($gameFruit);

        $this->roomLogService->createItemLog(
            PlantLogEnum::PLANT_NEW_FRUIT,
            $room,
            $gameFruit,
            VisibilityEnum::PUBLIC,
            $dateTime
        );
    }

    private function addOxygen(GameItem $gamePlant, PlantEffect $plantEffect)
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
        if (($oxygen = $plantEffect->getOxygen())) {
            $daedalus = $place->getDaedalus();
            $daedalus->setOxygen($daedalus->getOxygen() + $oxygen);
        }
    }
}
