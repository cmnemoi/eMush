<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Error;
use Mush\Action\Entity\ActionParameters;
use Mush\Player\Entity\Player;
use Mush\Item\Entity\Item;
use Mush\Item\Service\ItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Action\Enum\ActionEnum;


class Consume extends Action {

  private Player $player;
  private Item $item;
  private ItemServiceInterface $itemService;
  private PlayerServiceInterface $playerService;

  /**
   * Take constructor.
   * @param ItemServiceInterface $itemService
   * @param PlayerServiceInterface $playerService
   */


   public function __construct(ItemServiceInterface $itemService, PlayerServiceInterface $playerService)
   {
       $this->itemService = $itemService;
       $this->playerService = $playerService;
   }

   public function loadParameters(Player $player, ActionParameters $actionParameters)
   {
       if (! $item = $actionParameters->getItem()) {
           throw new \InvalidArgumentException('Invalid item parameter');
       }
       $this->player = $player;
       $this->item = $item;
   }


    public function canExecute(): bool
    {
      return $this->item->hasActions(ActionEnum::CONSUME) &&
      !$this->player->hasStatus('full'); // TODO: replace this with StatusEnum::full when it becomes available
      ;
    }

    protected function apply(): ActionResult {

      // TODO: if charges remove a charge

      // if no charges consume item
      $this->item->setPlayer(null);
      $this->item->setRoom(null);

      // TODO: apply effects to player
      

      return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        // TODO: Implement createLog() method.
    }



}





 ?>
