<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
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
        DaedalusServiceInterface $daedalusService
    ) {
        parent::__construct($eventDispatcher);

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

    public function canExecute(): bool
    {
        $gameConfig = $this->player->getDaedalus()->getGameConfig();

        return $this->player->canReachEquipment($this->gameEquipment) &&
            $this->gameEquipment->getEquipment()->hasAction(ActionEnum::RETRIEVE_OXYGEN) &&
            $this->gameEquipmentService->isOperational($this->gameEquipment) &&
            $this->player->canReachEquipment($this->gameEquipment) &&
            $this->player->getItems()->count() < $gameConfig->getMaxItemInInventory() &&
            $this->player->getDaedalus()->getOxygen() > 0
            ;
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

        return new Success(ActionLogEnum::RETRIEVE_OXYGEN, VisibilityEnum::COVERT, $target);
    }
}
