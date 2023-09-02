<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatusEventSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function getSubscribedEvents()
    {
        return [
            StatusEvent::STATUS_CHARGE_UPDATED => 'onStatusChargeUpdated',
        ];
    }

    public function onStatusChargeUpdated(StatusEvent $statusEvent)
    {
        // only handle patrol ship armor status
        if ($statusEvent->getStatusName() !== EquipmentStatusEnum::PATROL_SHIP_ARMOR) {
            return;
        }

        /** @var GameEquipment $patrolShip */
        $patrolShip = $statusEvent->getStatusHolder();
        /** @var Player $patrolShipPilot */
        $patrolShipPilot = $statusEvent->getAuthor();

        if (!$patrolShipPilot) {
            throw new \RuntimeException('Event should have author');
        }

        $this->gameEquipmentService->handlePatrolShipDestruction(
            $patrolShip,
            $patrolShipPilot,
            $statusEvent->getTags(),
        );
    }
}
