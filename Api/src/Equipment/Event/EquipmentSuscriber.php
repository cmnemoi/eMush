<?php

namespace Mush\Equipment\Event;

use Error;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;
    private GameConfig $gameConfig;

    public function __construct(
        RoomLogServiceInterface $roomLogService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->roomLogService = $roomLogService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_CREATED => 'onEquipmentCreated',
        ];
    }

    public function onItemCreated(EquipmentEvent $event): void
    {
        if (!$player = $event->getPlayer()) {
            throw new Error('Player should be provided');
        }

        $equipment = $event->getEquipment();
        if (!$equipment instanceof GameItem) {
            $equipment->setRoom($player->getRoom());
        } elseif ($player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()) {
            $equipment->setPlayer($player);
        } else {
            $equipment->setRoom($player->getRoom());
            $this->roomLogService->createEquipmentLog(
                LogEnum::OBJECT_FELT,
                $player->getRoom(),
                $player,
                $equipment,
                VisibilityEnum::PUBLIC,
            );
        }
    }
}
