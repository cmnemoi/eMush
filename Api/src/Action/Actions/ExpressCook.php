<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExpressCook extends Action
{
    protected string $name = ActionEnum::EXPRESS_COOK;

    private GameEquipment $gameEquipment;

    private RoomLogServiceInterface $roomLogService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
        $this->gameConfig = $gameConfigService->getConfig();

        $this->actionCost->setActionPointCost(0);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
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
        return ($this->gameEquipment->getEquipment()->getName() === GameRationEnum::STANDARD_RATION ||
             $this->gameEquipment->getStatusByName(EquipmentStatusEnum::FROZEN)) &&
             $this->player->canReachEquipment($this->gameEquipment) &&
             !$this->gameEquipmentService
                    ->getOperationalEquipmentsByName(ToolItemEnum::MICROWAVE, $this->player, ReachEnum::SHELVE_NOT_HIDDEN)->isEmpty()
        ;
    }

    protected function applyEffects(): ActionResult
    {
        if ($this->gameEquipment->getEquipment()->getName() === GameRationEnum::STANDARD_RATION) {
            $newItem = $this->gameEquipmentService->createGameEquipmentFromName(GameRationEnum::COOKED_RATION, $this->player->getDaedalus());
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
            $frozenStatus = $this->gameEquipment->getStatusByName(EquipmentStatusEnum::FROZEN);

            $this->gameEquipment->removeStatus($frozenStatus);
            $this->gameEquipmentService->persist($this->gameEquipment);
        }

        $microwave = $this->gameEquipmentService->getOperationalEquipmentsByName(
             ToolItemEnum::MICROWAVE,
             $this->player,
             ReachEnum::SHELVE_NOT_HIDDEN
             )->first();
        $microwave->getStatusByName(EquipmentStatusEnum::CHARGES)->addCharge(-1);

        //@TODO add effect on the link with sol

        $this->statusService->persist($microwave->getStatusByName(EquipmentStatusEnum::CHARGES));

        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createEquipmentLog(
            ActionEnum::EXPRESS_COOK,
            $this->player->getRoom(),
            $this->player,
            $this->gameEquipment,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
