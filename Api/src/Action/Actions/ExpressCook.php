<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Enum\ReachEnum;
use Mush\Item\Enum\GameRationEnum;
use Mush\Item\Enum\ToolItemEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExpressCook extends Action
{
    protected string $name = ActionEnum::EXPRESS_COOK;

    private GameItem $gameItem;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $gameItemService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $gameItemService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameItemService = $gameItemService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
        $this->gameConfig = $gameConfigService->getConfig();

        $this->actionCost->setActionPointCost(0);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!($item = $actionParameters->getItem())) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }

        $this->player = $player;
        $this->gameItem = $item;
    }

    public function canExecute(): bool
    {
        return ($this->gameItem->getItem()->getName()===GameRationEnum::STANDARD_RATION ||
             $this->gameItem->getStatusByName(ItemStatusEnum::FROZEN)) &&
             $this->player->canReachItem($this->gameItem) &&
             !$this->gameItemService
                    ->canUseItemByName(ToolItemEnum::MICROWAVE, $this->player, $this->player->getDaedalus(), ReachEnum::SHELVE_NOT_HIDDEN)->isEmpty()
        ;
    }

    protected function applyEffects(): ActionResult
    {
        if ($this->gameItem->getItem()->getName() === GameRationEnum::STANDARD_RATION) {
            $newItem = $this->gameItemService->createGameItemFromName(GameRationEnum::COOKED_RATION, $this->player->getDaedalus());
            if ($this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()) {
                $newItem->setPlayer($this->player);
            } else {
                $newItem->setPlayer($this->player->getRoom());
            }

            foreach ($this->gameItem->getStatuses() as $status) {
                $newItem->addStatus($status);
                $status->setItem($newItem);
                $this->statusService->persist($status);
            }

            $this->gameItem->setRoom(null);
            $this->gameItem->setPlayer(null);

            $this->gameItemService->delete($this->gameItem);
            $this->gameItemService->persist($newItem);
        } else {
            $frozenStatus = $this->gameItem->getStatusByName(ItemStatusEnum::FROZEN);

            $this->gameItem->removeStatus($frozenStatus);
            $this->gameItemService->persist($this->gameItem);
        }

        $microwave = $this->gameItemService->canUseItemByName(
             ToolItemEnum::MICROWAVE,
             $this->player,
             $this->player->getDaedalus(),
             ReachEnum::SHELVE_NOT_HIDDEN
             )->first();
        $microwave->getStatusByName(ItemStatusEnum::CHARGES)->setCharge($microwave->getStatusByName(ItemStatusEnum::CHARGES)->getCharge() - 1);

        //@TODO add effect on the link with sol

        $this->statusService->persist($microwave->getStatusByName(ItemStatusEnum::CHARGES));

        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::EXPRESS_COOK,
            $this->player->getRoom(),
            $this->player,
            $this->gameItem,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
