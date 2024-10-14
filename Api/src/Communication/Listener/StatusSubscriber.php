<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class StatusSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NeronMessageServiceInterface $neronMessageService,
        private ChannelServiceInterface $channelService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        match ($event->getStatusName()) {
            EquipmentStatusEnum::BROKEN => $this->handleBrokenStatusApplied($event),
            StatusEnum::FIRE => $this->neronMessageService->createNewFireMessage($event->getDaedalus(), $event->getTime(), $event->getTags()),
            PlayerStatusEnum::LOST => $this->channelService->updatePlayerPrivateChannels($event->getPlayerStatusHolder(), PlayerStatusEnum::LOST, $event->getTime()),
            default => null,
        };
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        match ($event->getStatusName()) {
            DaedalusStatusEnum::NO_GRAVITY_REPAIRED => $this->neronMessageService->createNeronMessage(
                messageKey: NeronMessageEnum::RESTART_GRAVITY,
                daedalus: $event->getDaedalus(),
                parameters: [],
                dateTime: $event->getTime()
            ),
            PlayerStatusEnum::MUSH => $this->channelService->removePlayerFromMushChannel($event->getPlayerStatusHolder()),
            default => null,
        };
    }

    private function handleBrokenStatusApplied(StatusEvent $event): void
    {
        $gameEquipment = $event->getGameEquipmentStatusHolder();
        $equipmentBrokenByCycleChange = $event->hasAllTags([EventEnum::NEW_CYCLE, EquipmentEvent::EQUIPMENT_BROKEN]);
        $equipmentBrokenByGreenJelly = $event->hasTag(EquipmentStatusEnum::SLIMED);

        if ($equipmentBrokenByCycleChange || $equipmentBrokenByGreenJelly) {
            $this->neronMessageService->createBrokenEquipmentMessage($gameEquipment, $event->getVisibility(), $event->getTime(), $event->getTags());
        }

        if ($gameEquipment->getName() === EquipmentEnum::COMMUNICATION_CENTER) {
            foreach ($gameEquipment->getPlace()->getPlayers() as $player) {
                $this->channelService->updatePlayerPrivateChannels($player, EquipmentStatusEnum::BROKEN, $event->getTime());
            }
        }
    }
}
