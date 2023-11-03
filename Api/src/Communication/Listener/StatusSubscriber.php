<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusSubscriber implements EventSubscriberInterface
{
    private NeronMessageServiceInterface $neronMessageService;
    private ChannelServiceInterface $channelService;

    public function __construct(
        NeronMessageServiceInterface $neronMessageService,
        ChannelServiceInterface $channelService,
    ) {
        $this->neronMessageService = $neronMessageService;
        $this->channelService = $channelService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();
        $time = $event->getTime();
        $equipmentBrokenByCycleChange = $event->hasAllTags(
            tags: [
                EventEnum::NEW_CYCLE,
                EquipmentEvent::EQUIPMENT_BROKEN,
            ],
        )
        && $holder instanceof GameEquipment;
        // $brokenByGreenJelly = $event->getReason() === EventEnum::GREEN_JELLY;

        switch ($event->getStatusName()) {
            case EquipmentStatusEnum::BROKEN:
                if (!$holder instanceof GameEquipment) {
                    throw new UnexpectedTypeException($holder, GameEquipment::class);
                }
                // @TODO : if ($brokenByGreenJelly || $equipmentBrokenByCycleChange)
                if ($equipmentBrokenByCycleChange) {
                    $this->neronMessageService->createBrokenEquipmentMessage($holder, $event->getVisibility(), $event->getTime());
                }

                // check if player needs to be expelled from private channels
                if ($holder->getName() === EquipmentEnum::COMMUNICATION_CENTER) {
                    foreach ($holder->getPlace()->getPlayers() as $player) {
                        $this->channelService->updatePlayerPrivateChannels($player, EquipmentStatusEnum::BROKEN, $time);
                    }
                }

                return;

            case StatusEnum::FIRE:
                $daedalus = $event->getDaedalus();
                $this->neronMessageService->createNewFireMessage($daedalus, $event->getTime());

                return;
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();

        switch ($event->getStatusName()) {
            case DaedalusStatusEnum::NO_GRAVITY_REPAIRED:
                $daedalus = $event->getDaedalus();

                $this->neronMessageService->createNeronMessage(
                    NeronMessageEnum::RESTART_GRAVITY,
                    $daedalus,
                    [],
                    $event->getTime()
                );

                return;
        }
    }
}
