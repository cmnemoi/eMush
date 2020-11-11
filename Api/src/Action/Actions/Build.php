<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Blueprint;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Build extends Action
{
    protected const NAME = ActionEnum::BUILD;

    private GameItem $item;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $gameItemService;
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $gameItemService,
        PlayerServiceInterface $playerService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameItemService = $gameItemService;
        $this->playerService = $playerService;
        $this->gameConfig = $gameConfigService->getConfig();
        $this->actionCost->setActionPointCost(3);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!$item = $actionParameters->getItem()) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }
        $this->player = $player;
        $this->item = $item;
    }

    public function canExecute(): bool
    {
        $blueprintType = $this->item->getItem()->getItemType(ItemTypeEnum::BLUEPRINT);
        //Check that the item is a blueprint and is reachable
        if (
            $blueprintType === null ||
            !$this->player->canReachItem($this->item)
        ) {
            return false;
        }
        //Check the availlability of the ingredients
        foreach ($blueprintType->getIngredients() as $itemName => $number) {
            if ($this->player->getReachableItemByName($itemName)->count() < $number) {
                return false;
            }
        }

        return true;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Blueprint $blueprintType */
        $blueprintType = $this->item->getItem()->getItemType(ItemTypeEnum::BLUEPRINT);

        // add the item in the player inventory or in the room if the inventory is full
        $blueprintObject = $this->gameItemService->createGameItem(
            $blueprintType->getItem(),
            $this->player->getDaedalus()
        );

        if ($this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()) {
            $blueprintObject->setPlayer($this->player);
        } else {
            $blueprintObject->setRoom($this->player->getRoom());
        }

        $this->gameItemService->persist($blueprintObject);

        // remove the used ingredients starting from the player inventory
        foreach ($blueprintType->getIngredients() as $itemName => $number) {
            for ($i = 0; $i < $number; ++$i) {
                if ($this->player->hasItemByName($itemName)) {
                    // @FIXME change to a random choice of the item
                    $ingredient = $this->player->getItems()
                        ->filter(fn (GameItem $gameItem) => $gameItem->getName() === $itemName)->first();
                    $this->player->removeItem($ingredient);
                } else {
                    // @FIXME change to a random choice of the item
                    $ingredient = $this->player->getRoom()->getItems()
                        ->filter(fn (GameItem $gameItem) => $gameItem->getName() === $itemName)->first();
                    $ingredient->setRoom(null);
                }
                $this->gameItemService->delete($ingredient);
            }
        }

        // remove the blueprint
        $this->item
            ->setRoom(null)
            ->setPlayer(null)
        ;

        $this->gameItemService->delete($this->item);

        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::BUILD,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }

    public function getActionName(): string
    {
        return self::NAME;
    }
}
