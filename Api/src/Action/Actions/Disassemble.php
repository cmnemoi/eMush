<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionCost;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Dismountable;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class Disassemble extends Action
{
    protected const NAME = ActionEnum::DISASSEMBLE;

    private GameItem $item;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $itemService;
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;

    public function __construct(
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $itemService,
        PlayerServiceInterface $playerService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->roomLogService = $roomLogService;
        $this->itemService = $itemService;
        $this->playerService = $playerService;
        $this->gameConfig = $gameConfigService->getConfig();
        $this->actionCost = new ActionCost();
        $this->actionCost->setActionPointCost(0);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (! $item = $actionParameters->getItem()) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }
        $this->player = $player;
        $this->item = $item;
        
        $dismountableType = $this->item->getItem()->getItemType(ItemTypeEnum::DISMOUNTABLE);
        $this->actionCost->setActionPointCost($dismountableType->getActionCost());
    }

    public function canExecute(): bool
    {
        $dismountableType = $this->item->getItem()->getItemType(ItemTypeEnum::DISMOUNTABLE);
        //Check that the item is reachable
        return ($dismountableType !== null ||
                    $this->player->canReachItem($this->item)); //||
                    //in_array(SkillEnum::TECHNICIAN, $this->player->getSkills()));
    }
        
        
        
        


    protected function applyEffects(): ActionResult
    {
        /** @var Blueprint $blueprintType */
        $dismountableType = $this->item->getItem()->getItemType(ItemTypeEnum::DISMOUNTABLE);
        
        
        // @TODO add the chances of success
        
        // add the item produced by disassembling
        foreach ($dismountableType->getProducts() as $productString => $number) {
            for ($i = 0; $i < $number; $i++) {
                $productItem=$this->itemService->createGameItemFromName($productString, $this->player->getDaedalus());
                if ($this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()) {
                       $productItem->setPlayer($this->player);
                } else {
                    $productItem->setRoom($this->player->getRoom());
                }
                $this->itemService->persist($productItem);
            }
        }
        
        
                
        
        
        // remove the dismanteled item
        $this->item
        ->setRoom(null)
        ->setPlayer(null)
        ;
        
        $this->itemService->delete($this->item);
        $this->playerService->persist($this->player);


        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::DISASSEMBLE,
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
