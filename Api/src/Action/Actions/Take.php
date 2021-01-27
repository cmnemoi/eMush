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
use Mush\Status\Enum\EquipmentStatusEnum as EnumEquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Take extends AbstractAction
{
    protected string $name = ActionEnum::TAKE;

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

        if (!($item = $actionParameters->getItem())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->gameItem = $item;
    }

    public function canExecute(): bool
    {
        /** @var ItemConfig $item */
        $item = $this->gameItem->getEquipment();

        $gameConfig = $this->player->getDaedalus()->getGameConfig();

        return $this->player->getRoom()->getEquipments()->contains($this->gameItem) &&
            $this->player->getItems()->count() < $gameConfig->getMaxItemInInventory() &&
            $item->hasAction(ActionEnum::TAKE)
            ;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var ItemConfig $item */
        $item = $this->gameItem->getEquipment();

        $this->gameItem->setRoom(null);
        $this->gameItem->setPlayer($this->player);

        // add BURDENED status if item is heavy
        if ($item->isHeavy()) {
            $this->statusService->createCoreStatus(PlayerStatusEnum::BURDENED, $this->player);
        }

        if ($hiddenStatus = $this->gameItem->getStatusByName(EnumEquipmentStatusEnum::HIDDEN)) {
            $this->gameItem->removeStatus($hiddenStatus);
            $this->player->removeStatus($hiddenStatus);
        }

        $this->gameEquipmentService->persist($this->gameItem);
        $this->playerService->persist($this->player);

        $target = new Target($this->gameItem->getName(), 'items');

        return new Success($target);
    }
}
