<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\ItemStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Search extends Action
{
    protected const NAME = ActionEnum::SEARCH;

    private GameItem $gameItem;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $gameItemService;
    private PlayerServiceInterface $playerService;
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

        $this->StatusServiceInterface =$statusService;
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
    	  if($this->player->getRoom()->getItems()->hasStatusByName(ItemStatusEnum::HIDDEN)->count()>0){
    	      $foundItem=$this->player->getRoom()->getItems()->hasStatusByName(ItemStatusEnum::HIDDEN)->first();
    	  }
    	  
    	  $hiddenStatus=$foundItem->getStatusByName(ItemStatusEnum::HIDDEN);
    	  

        $foundItem->removeStatus($hiddenStatus);
        $hiddenBy=$hiddenStatus->getPlayer();
        $hiddenBy->removeStatus($hiddenStatus);
        
        $foundItem->setRoom(null);
        $foundItem->setPlayer($this->player);
    	  
        $this->itemService->persist($foundItem);
        $this->playerService->persist($this->player);
        $this->playerService->persist($hiddenBy);
        
        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::HIDE,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::COVERT,
            new \DateTime('now')
        );
    }

    public function getActionName(): string
    {
        return self::NAME;
    }
}
