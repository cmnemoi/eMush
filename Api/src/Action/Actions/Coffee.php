<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Coffee extends AbstractAction
{
    protected string $name = ActionEnum::COFFEE;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->gameEquipment = $equipment;
    }

    public function canExecute(): bool
    {
        return $this->gameEquipment->getActions()->contains($this->action) &&
            $this->player->canReachEquipment($this->gameEquipment)
            ;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $newItem */
        $newItem = $this->gameEquipmentService
            ->createGameEquipmentFromName(GameRationEnum::COFFEE, $this->player->getDaedalus())
        ;

        $equipmentEvent = new EquipmentEvent($newItem, VisibilityEnum::HIDDEN);
        $equipmentEvent->setPlayer($this->player);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $this->gameEquipmentService->persist($newItem);

        $chargeStatus = $this->gameEquipment->getStatusByName(EquipmentStatusEnum::CHARGES);

        if ($chargeStatus instanceof ChargeStatus) {
            $chargeStatus->addCharge(-1);
            $this->statusService->persist($chargeStatus);
        }

        return new Success(ActionLogEnum::COFFEE_SUCCESS, VisibilityEnum::PUBLIC);
    }
}
