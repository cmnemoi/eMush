<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\StatusEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Item\Entity\Item;
use Mush\Item\Service\ItemServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;

class Take extends Action
{
    private Player $player;
    private Item $item;
    private ItemServiceInterface $itemService;
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;

    /**
     * Take constructor.
     * @param ItemServiceInterface $itemService
     * @param PlayerServiceInterface $playerService
     * @param GameConfigServiceInterface $gameConfigService
     */
    public function __construct(
        ItemServiceInterface $itemService,
        PlayerServiceInterface $playerService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->itemService = $itemService;
        $this->playerService = $playerService;
        $this->gameConfig = $gameConfigService->getConfig();
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
        return $this->player->getRoom()->getItems()->contains($this->item) &&
            $this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory() &&
            $this->item->isMovable()
            ;
    }

    protected function apply(): ActionResult
    {
        $this->item->setRoom(null);
        $this->item->setPlayer($this->player);

        // add BURDENED status if item is heavy and player hasn't SOLID skill
        if ($this->item->isHeavy() &&
            !in_array(SkillEnum::SOLID, $this->player->getSkills())
        ) {
            $this->player->getSkills()[] = StatusEnum::BURDENED;
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
