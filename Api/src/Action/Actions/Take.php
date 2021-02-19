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

        if (!($item = $actionParameters->getItem())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->gameItem = $item;
    }

    public function isVisible(): bool
    {
        return $this->player->canReachEquipment($this->gameItem) &&
            !$this->player->getItems()->contains($this->gameItem) &&
            $this->gameItem->getEquipment()->hasAction($this->name) &&
            parent::isVisible()
        ;
    }

    public function cannotExecuteReason(): ?string
    {
        $gameConfig = $this->player->getDaedalus()->getGameConfig();
        if ($this->player->getItems()->count() >= $gameConfig->getMaxItemInInventory()) {
            return ActionImpossibleCauseEnum::FULL_INVENTORY;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        /** @var ItemConfig $item */
        $item = $this->gameItem->getEquipment();

        $this->gameItem->setPlace(null);
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
