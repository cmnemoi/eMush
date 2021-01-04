<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InsertOxygen extends AbstractAction
{
    protected string $name = ActionEnum::INSERT_OXYGEN;

    private GameItem $gameItem;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private DaedalusServiceInterface $daedalusService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        DaedalusServiceInterface $daedalusService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->daedalusService = $daedalusService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        if (!$item = $actionParameters->getItem()) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }

        $this->player = $player;
        $this->gameItem = $item;
    }

    public function canExecute(): bool
    {
        return $this->player->canReachEquipment($this->gameItem) &&
            $this->gameItem->getEquipment()->getName() === ItemEnum::OXYGEN_CAPSULE &&
            $this->gameEquipmentService->getOperationalEquipmentsByName(EquipmentEnum::OXYGEN_TANK, $this->player) &&
            $this->player->getDaedalus()->getOxygen() < $this->gameConfig->getMaxOxygen()
            ;
    }

    protected function applyEffects(): ActionResult
    {
        $this->gameItem->setPlayer(null);

        $this->gameEquipmentService->delete($this->gameItem);

        $this->daedalusService->changeOxygenLevel($this->player->getDaedalus(), 1);

        return new Success(ActionLogEnum::INSERT_OXYGEN, VisibilityEnum::PUBLIC);
    }
}
