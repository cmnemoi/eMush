<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Drop extends AbstractAction
{
    protected string $name = ActionEnum::DROP;

    private GameItem $gameItem;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!$item = $actionParameters->getItem()) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }

        $this->gameItem = $item;
    }

    public function canExecute(): bool
    {
        $gameEquipment = $this->gameItem->getEquipment();

        return $this->player->getItems()->contains($this->gameItem) &&
            $gameEquipment instanceof ItemConfig &&
            $gameEquipment->hasAction(ActionEnum::DROP)
            ;
    }

    protected function applyEffects(): ActionResult
    {
        $this->gameItem->setRoom($this->player->getRoom());
        $this->gameItem->setPlayer(null);

        // Remove BURDENED status if no other heavy item in the inventory
        if (($burdened = $this->player->getStatusByName(PlayerStatusEnum::BURDENED)) &&
            $this->player->getItems()->filter(function (GameItem $item) {
                /** @var ItemConfig $itemConfig */
                $itemConfig = $item->getEquipment();

                return $itemConfig->isHeavy();
            })->isEmpty()
        ) {
            $this->player->removeStatus($burdened);
        }

        $this->gameEquipmentService->persist($this->gameItem);
        $this->playerService->persist($this->player);

        $target = new Target($this->gameItem->getName(), 'items');

        return new Success($target);
    }
}
