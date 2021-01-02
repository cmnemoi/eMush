<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Hyperfreeze extends Action
{
    protected string $name = ActionEnum::HYPERFREEZE;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
        $this->gameConfig = $gameConfigService->getConfig();

        $this->actionCost->setActionPointCost(1);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        if (!($equipment = $actionParameters->getItem()) &&
            !($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->player = $player;
        $this->gameEquipment = $equipment;
    }

    public function canExecute(): bool
    {
        /** @var Ration $rationType */
        $rationType = $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::RATION);

        return $rationType &&
            $rationType->isPerishable() &&
            $this->player->canReachEquipment($this->gameEquipment) &&
            !$this->gameEquipmentService
                ->getOperationalEquipmentsByName(ToolItemEnum::SUPERFREEZER, $this->player, ReachEnum::SHELVE_NOT_HIDDEN)->isEmpty() &&
            !$this->gameEquipment->getStatusByName(EquipmentStatusEnum::FROZEN)
            ;
    }

    protected function applyEffects(): ActionResult
    {
        if ($this->gameEquipment->getEquipment()->getName() === GameRationEnum::COOKED_RATION ||
            $this->gameEquipment->getEquipment()->getName() === GameRationEnum::ALIEN_STEAK) {
            /** @var GameItem $newItem */
            $newItem = $this->gameEquipmentService
                ->createGameEquipmentFromName(GameRationEnum::STANDARD_RATION, $this->player->getDaedalus())
            ;
            if ($this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()) {
                $newItem->setPlayer($this->player);
            } else {
                $newItem->setRoom($this->player->getRoom());
            }

            foreach ($this->gameEquipment->getStatuses() as $status) {
                $newItem->addStatus($status);
                $status->setGameEquipment($newItem);
                $this->statusService->persist($status);
            }

            $this->gameEquipment->removeLocation();
            $this->gameEquipmentService->delete($this->gameEquipment);
            $this->gameEquipmentService->persist($newItem);
        } else {
            $frozenStatus = new Status();
            $frozenStatus
                ->setName(EquipmentStatusEnum::FROZEN)
                ->setVisibility(VisibilityEnum::PUBLIC)
                ->setGameEquipment($this->gameEquipment);

            $this->gameEquipment->addStatus($frozenStatus);
            $this->gameEquipmentService->persist($this->gameEquipment);
        }

        $this->playerService->persist($this->player);

        return new Success(ActionLogEnum::HYPERFREEZE_SUCCESS, VisibilityEnum::PUBLIC);
    }
}
