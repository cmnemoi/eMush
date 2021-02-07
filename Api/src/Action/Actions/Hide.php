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
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Hide extends AbstractAction
{
    protected string $name = ActionEnum::HIDE;

    private GameItem $gameItem;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
        $this->playerService = $playerService;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!($item = $actionParameters->getItem())) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }

        $this->gameItem = $item;
    }

    public function canExecute(): bool
    {
        /** @var ItemConfig $itemConfig */
        $itemConfig = $this->gameItem->getEquipment();

        //Check that the item is reachable
        return $this->gameItem->getStatusByName(EquipmentStatusEnum::HIDDEN) === null &&
            $itemConfig->isHideable() &&
            $this->player->canReachEquipment($this->gameItem)
            ;
    }

    protected function applyEffects(): ActionResult
    {
        $this->statusService->createCoreStatus(
            EquipmentStatusEnum::HIDDEN,
            $this->gameItem,
            $this->player,
            VisibilityEnum::PRIVATE,
        );

        if ($this->gameItem->getPlayer()) {
            $this->gameItem->setPlayer(null);
            $this->gameItem->setPlace($this->player->getPlace());
        }

        $this->gameEquipmentService->persist($this->gameItem);
        $this->playerService->persist($this->player);

        $target = new Target($this->gameItem->getName(), 'items');

        return new Success($target);
    }
}
