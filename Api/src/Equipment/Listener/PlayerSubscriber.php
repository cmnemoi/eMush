<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Enum\EquipmentEventReason;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\EquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EquipmentServiceInterface $equipmentService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        EquipmentServiceInterface $equipmentService,
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->equipmentService = $equipmentService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
        ];
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $this->handleCreateSchrodinger($event);
    }

    public function handleCreateSchrodinger(PlayerEvent $event): void
    {
        if ($event->getPlayer()->hasStatus(PlayerStatusEnum::CAT_OWNER)) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                ItemEnum::SCHRODINGER,
                $event->getPlayer()->getPlace(),
                [EquipmentEventReason::AWAKEN_SCHRODINGER],
                $event->getTime(),
                VisibilityEnum::PUBLIC,
                $event->getPlayer()
            );
        }
    }
}
