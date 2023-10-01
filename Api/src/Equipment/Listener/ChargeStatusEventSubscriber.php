<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\ChargeStatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChargeStatusEventSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            VariableEventInterface::CHANGE_VARIABLE => ['onStatusChargeUpdated', -100],
            VariableEventInterface::SET_VALUE => ['onStatusChargeUpdated', -100],
        ];
    }

    public function onStatusChargeUpdated(VariableEventInterface $statusEvent): void
    {
        // only handle patrol ship armor status
        if (!($statusEvent instanceof ChargeStatusEvent)
            || $statusEvent->getStatusName() !== EquipmentStatusEnum::PATROL_SHIP_ARMOR
            || !$statusEvent->getVariable()->isMin()
        ) {
            return;
        }

        /** @var GameEquipment $patrolShip */
        $patrolShip = $statusEvent->getStatusHolder();

        $this->gameEquipmentService->handlePatrolShipDestruction(
            $patrolShip,
            $statusEvent->getAuthor(),
            $statusEvent->getTags(),
        );
    }
}
