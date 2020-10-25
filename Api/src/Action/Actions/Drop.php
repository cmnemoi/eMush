<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Game\Enum\StatusEnum;
use Mush\Item\Entity\Item;
use Mush\Item\Service\ItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;

class Drop extends Action
{
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
        return $this->player->getItems()->contains($this->item);
    }

    protected function apply(): ActionResult
    {
        $this->item->setRoom($this->player->getRoom());
        $this->item->setPlayer(null);

        // Remove BURDENED status if no other heavy item in the inventory
        if (in_array(StatusEnum::BURDENED, $this->player->getStatuses()) &&
            $this->player->getItems()->exists(fn (Item $item) => $item->isHeavy())
        ) {
            $this->player->setStatuses(\array_diff($this->player->getStatuses(), [StatusEnum::BURDENED]));
        }

        $this->itemService->persist($this->item);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        // TODO: Implement createLog() method.
    }
}
