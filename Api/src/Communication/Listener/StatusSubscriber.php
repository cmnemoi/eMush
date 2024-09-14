<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\NeronMessageServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
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

        switch ($event->getStatusName()) {
            case EquipmentStatusEnum::BROKEN:
                $this->handleBrokenStatusApplied($event);

                return;

            case StatusEnum::FIRE:
                $this->neronMessageService->createNewFireMessage($event->getDaedalus(), $event->getTime(), $event->getTags());

                return;

            case PlayerStatusEnum::LOST:
                /** @var Player $player */
                $player = $holder;
                $this->channelService->updatePlayerPrivateChannels($player, PlayerStatusEnum::LOST, $time);
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

            case PlayerStatusEnum::MUSH:
                /** @var Player $player */
                $player = $holder;
                $this->channelService->removePlayerFromMushChannel($player);

                return;
        }
    }

    private function handleBrokenStatusApplied(StatusEvent $event): void
    {
        $holder = $event->getStatusHolder();

        if (!$holder instanceof GameEquipment) {
            throw new UnexpectedTypeException($holder, GameEquipment::class);
        }

        $equipmentBrokenByCycleChange = $event->hasAllTags(
            tags: [
                EventEnum::NEW_CYCLE,
                EquipmentEvent::EQUIPMENT_BROKEN,
            ],
        ) && $holder instanceof GameEquipment;

        if ($equipmentBrokenByCycleChange) {
            $this->neronMessageService->createBrokenEquipmentMessage($holder, $event->getVisibility(), $event->getTime(), $event->getTags());
        }

        // check if player needs to be expelled from private channels
        if ($holder->getName() === EquipmentEnum::COMMUNICATION_CENTER) {
            foreach ($holder->getPlace()->getPlayers() as $player) {
                $this->channelService->updatePlayerPrivateChannels($player, EquipmentStatusEnum::BROKEN, $event->getTime());
            }
        }
    }
}
