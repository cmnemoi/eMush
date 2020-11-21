<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\ItemStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Search extends Action
{
    protected string $name = ActionEnum::SEARCH;

    private ?GameItem $itemFound = null;

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

        $this->actionCost->setActionPointCost(1);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        $this->player = $player;
    }

    public function canExecute(): bool
    {
        return true;
    }

    protected function applyEffects(): ActionResult
    {
        $hiddenItems = $this->player->getRoom()->getItems()->getByStatusName(ItemStatusEnum::HIDDEN);
        if (!$hiddenItems->isEmpty()) {
            $this->itemFound = $hiddenItems->first();

            $hiddenStatus = $this->itemFound->getStatusByName(ItemStatusEnum::HIDDEN);

            $this->itemFound->removeStatus($hiddenStatus);

            $this->gameItemService->persist($this->itemFound);

            return new Success();
        } else {
            return new Fail();
        }
    }

    protected function createLog(ActionResult $actionResult): void
    {
        if ($actionResult instanceof Success) {
            $this->roomLogService->createItemLog(
                ActionEnum::SEARCH,
                $this->player->getRoom(),
                $this->player,
                $this->itemFound,
                VisibilityEnum::COVERT,
                new \DateTime('now')
            );
        } else {
            $this->roomLogService->createPlayerLog(
                ActionEnum::SEARCH,
                $this->player->getRoom(),
                $this->player,
                VisibilityEnum::COVERT,
                new \DateTime('now')
            );
        }
    }
}
