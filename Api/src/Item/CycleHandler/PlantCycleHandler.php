<?php

namespace Mush\Item\CycleHandler;

use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Status\Enum\StatusEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\PlantEffect;
use Mush\Item\Enum\ItemEnum;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Item\Service\ItemEffectServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Service\StatusServiceInterface;

class PlantCycleHandler implements CycleHandlerInterface
{
    private GameItemServiceInterface $gameItemService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private GameConfig $gameConfig;
    private StatusServiceInterface $statusService;
    private ItemEffectServiceInterface $itemEffectService;

    private const DISEASE_PERCENTAGE = 3;

    public function __construct(
        GameItemServiceInterface $gameItemService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        GameConfigServiceInterface $gameConfigService,
        StatusServiceInterface $statusService,
        ItemEffectServiceInterface $itemEffectService
    ) {
        $this->gameItemService = $gameItemService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->itemEffectService = $itemEffectService;
        $this->statusService = $statusService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function handleNewCycle($gamePlant, $daedalus, \DateTime $dateTime)
    {
        if (!$gamePlant instanceof GameItem) {
            return;
        }
        $plantType = $gamePlant->getItem()->getItemType(ItemEnum::PLANT);
        if (null === $gamePlant || !$plantType instanceof Plant) {
            return;
        }

        if ($this->randomService->randomPercent() <= self::DISEASE_PERCENTAGE) {
            $diseased = new Status();
            $diseased
                ->setName(ItemStatusEnum::PLANT_DISEASED)
                ->setVisibility(VisibilityEnum::PUBLIC)
                ->setGameItem($gamePlant)
            ;
            $gamePlant->addStatus($diseased);
        }

        $plantEffect = $this->itemEffectService->getPlantEffect($plantType, $daedalus);

        if (
            ($youngStatus = $gamePlant->getStatusByName(ItemStatusEnum::PLANT_YOUNG)) &&
            $youngStatus->getCharge() >= $plantEffect->getMaturationTime()
        ) {
            $gamePlant->removeStatus($youngStatus);
            $this->roomLogService->createItemLog(
                PlantLogEnum::PLANT_MATURITY,
                $gamePlant->getRoom() ?? $gamePlant->getPlayer()->getRoom(),
                $gamePlant,
                VisibilityEnum::PUBLIC,
                $dateTime
            );
        }

        $this->gameItemService->persist($gamePlant);
    }

    public function handleNewDay($gamePlant, $daedalus, \DateTime $dateTime)
    {
        if (!$gamePlant instanceof GameItem) {
            return;
        }
        $plantType = $gamePlant->getItem()->getItemType(ItemEnum::PLANT);
        if (null === $gamePlant || !$plantType instanceof Plant) {
            return;
        }

        $plantEffect = $this->itemEffectService->getPlantEffect($plantType, $daedalus);

        $plantStatus = $gamePlant->getStatuses();

        //If plant is young, dried or diseased, do not produce oxygen
        if (
            $plantStatus->filter(
                fn (Status $status) => in_array(
                    $status->getName(),
                    [ItemStatusEnum::PLANT_DRIED, ItemStatusEnum::PLANT_DISEASED, ItemStatusEnum::PLANT_YOUNG]
                )
            )->isEmpty()
        ) {
            $this->addOxygen($gamePlant, $plantEffect);
            if (
                $plantStatus->filter(fn (Status $status) => in_array(
                    $status->getName(),
                    [ItemStatusEnum::PLANT_THIRSTY]
                ))->isEmpty()
            ) {
                $this->addFruit($gamePlant, $plantType, $dateTime);
            }
        }

        $this->handleStatus($gamePlant, $dateTime);

        $this->gameItemService->persist($gamePlant);
    }

    private function handleStatus(GameItem $gamePlant, \DateTime $dateTime)
    {
        // If plant was thirsty, become dried
        if (($thirsty = $gamePlant->getStatusByName(ItemStatusEnum::PLANT_THIRSTY)) !== null) {
            $gamePlant->removeStatus($thirsty);
            $driedStatus = $this->statusService->createCoreItemStatus(ItemStatusEnum::PLANT_DRIED, $gamePlant);
            $gamePlant->addStatus($driedStatus);
        // If plant was dried, become hydropot
        } elseif ($gamePlant->getStatusByName(ItemStatusEnum::PLANT_DRIED) !== null) {
            $this->handleDriedPlant($gamePlant, $dateTime);
        // If plant was not thirsty or dried become thirsty
        } else {
            $thirstyStatus = $this->statusService->createCoreItemStatus(ItemStatusEnum::PLANT_THIRSTY, $gamePlant);
            $gamePlant->addStatus($thirstyStatus);
        }
    }

    private function handleDriedPlant(GameItem $gamePlant, \DateTime $dateTime)
    {
        // If plant is not in a room, it is in player inventory
        $place = $gamePlant->getRoom() ? $gamePlant->getRoom() : $gamePlant->getPlayer();
        // Create a new hydropot
        $hydropot = $this->gameItemService->createGameItemFromName(ItemEnum::HYDROPOT, $place->getDaedalus());

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
        $this->gameItemService->delete($gamePlant); // Remove plant
        $this->gameItemService->persist($hydropot); // Add hydropot
    }

    private function addFruit(GameItem $gamePlant, Plant $plantType, \DateTime $dateTime)
    {
        //If plant is young, thirsty, dried or diseased, do not produce fruit
        if (
            !$gamePlant->getStatuses()
            ->filter(
                fn (Status $status) => in_array(
                    $status->getName(),
                    [
                        ItemStatusEnum::PLANT_DRIED,
                        ItemStatusEnum::PLANT_DISEASED,
                        ItemStatusEnum::PLANT_YOUNG,
                        ItemStatusEnum::PLANT_THIRSTY,
                    ]
                )
            )
            ->isEmpty()
        ) {
            return;
        }
        // If plant is not in a room, it is in player inventory
        $place = $gamePlant->getRoom() ?? $gamePlant->getPlayer();
        $room = $place;

        // Create a new fruit
        $gameFruit = $this->gameItemService->createGameItem($plantType->getFruit(), $place->getDaedalus());

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

        $this->gameItemService->persist($gameFruit);

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
        // If plant is not in a room, it is in player inventory
        $place = $gamePlant->getRoom() ? $gamePlant->getRoom() : $gamePlant->getPlayer();

        //Add Oxygen
        if (($oxygen = $plantEffect->getOxygen())) {
            $daedalus = $place->getDaedalus();
            $daedalus->setOxygen($daedalus->getOxygen() + $oxygen);
        }
    }
}
