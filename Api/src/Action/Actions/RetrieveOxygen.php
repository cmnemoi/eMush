<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Target;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RetrieveOxygen extends AbstractAction
{
    protected string $name = ActionEnum::RETRIEVE_OXYGEN;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private DaedalusServiceInterface $daedalusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        DaedalusServiceInterface $daedalusService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->daedalusService = $daedalusService;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!$equipment = $actionParameters->getEquipment()) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->gameEquipment = $equipment;
    }

    public function isVisible(): bool
    {
        if (!$this->player->canReachEquipment($this->gameEquipment) ||
            !$this->gameEquipment->getEquipment()->hasAction($this->name) ||
            $this->player->getDaedalus()->getOxygen() <= 0
        ) {
            return false;
        }

        return parent::isVisible();
    }

    public function cannotExecuteReason(): ?string
    {
        if ($this->gameEquipment->isBroken()) {
            return ActionImpossibleCauseEnum::BROKEN_EQUIPMENT;
        }

        $gameConfig = $this->player->getDaedalus()->getGameConfig();
        if ($this->player->getItems()->count() >= $gameConfig->getMaxItemInInventory()) {
            return ActionImpossibleCauseEnum::FULL_INVENTORY;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        $gameItem = $this->gameEquipmentService->createGameEquipmentFromName(ItemEnum::OXYGEN_CAPSULE, $this->player->getDaedalus());

        if (!$gameItem instanceof GameItem) {
            throw new \LogicException('invalid GameItem');
        }

        $gameItem->setPlayer($this->player);

        $this->gameEquipmentService->persist($gameItem);

        $this->daedalusService->changeOxygenLevel($this->player->getDaedalus(), -1);

        $target = new Target($this->gameEquipment->getName(), 'items');

        return new Success($target);
    }
}
