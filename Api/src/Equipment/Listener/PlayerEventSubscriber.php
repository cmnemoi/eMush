<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerEventSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(GameEquipmentServiceInterface $gameEquipmentService)
    {
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::DEATH_PLAYER => 'onDeathPlayer',
        ];
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        if (!$event->hasTag(EndCauseEnum::SPACE_BATTLE)) {
            return;
        }

        $player = $event->getPlayer();
        $patrolShipPlace = $player->getPlace();
        if (!RoomEnum::getPatrolships()->contains($patrolShipPlace->getName())) {
            throw new \LogicException('Player should be in a patrol ship');
        }
        /** @var GameEquipment $patrolShip */
        $patrolShip = $patrolShipPlace->getEquipments()->filter(fn (GameEquipment $equipment) => $equipment->getEquipment()->hasMechanicByName(EquipmentMechanicEnum::PATROL_SHIP))->first();
        if (!$patrolShip) {
            throw new \LogicException('Patrol ship place should have a patrol ship for this event');
        }

        $this->gameEquipmentService->handlePatrolShipDestruction($patrolShip, $player, $event->getTags());
    }
}
