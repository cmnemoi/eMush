<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InsertOxygen extends AbstractAction
{
    protected string $name = ActionEnum::INSERT_OXYGEN;

    private GameItem $gameItem;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private DaedalusServiceInterface $daedalusService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        DaedalusServiceInterface $daedalusService,
        ActionServiceInterface $actionService,
        GearToolServiceInterface $gearToolService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->daedalusService = $daedalusService;
        $this->gearToolService = $gearToolService;
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
        $gameConfig = $this->player->getDaedalus()->getGameConfig();

        return $this->player->canReachEquipment($this->gameItem) &&
            $this->gameItem->getEquipment()->getName() === ItemEnum::OXYGEN_CAPSULE &&
            $this->gearToolService->getUsedTool($this->player, $this->action->getName()) !== null &&
            $this->player->getDaedalus()->getOxygen() < $gameConfig->getDaedalusConfig()->getMaxOxygen()
            ;
    }

    protected function applyEffects(): ActionResult
    {
        $this->gameItem->setPlayer(null);

        $this->gameEquipmentService->delete($this->gameItem);

        $this->daedalusService->changeOxygenLevel($this->player->getDaedalus(), 1);

        return new Success();
    }
}
