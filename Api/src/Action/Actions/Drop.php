<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
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
        StatusServiceInterface $statusService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

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

    public function isVisible(): bool
    {
        /** @var ItemConfig $itemConfig */
        $itemConfig = $this->gameItem->getEquipment();
        if (!$this->player->getItems()->contains($this->gameItem) ||
            !($itemConfig instanceof ItemConfig) ||
            !$itemConfig->hasAction(ActionEnum::DROP)
        ) {
            return false;
        }

        return parent::isVisible();
    }

    public function isImpossible(): ?string
    {
        if ($this->player->getPlace()->getType() !== PlaceTypeEnum::ROOM) {
            return ActionImpossibleCauseEnum::NO_SHELVING_UNIT;
        }

        return parent::isImpossible();
    }

    protected function applyEffects(): ActionResult
    {
        $this->gameItem->setPlace($this->player->getPlace());
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
