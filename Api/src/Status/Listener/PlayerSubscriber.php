<?php

namespace Mush\Status\Listener;

use Mush\Communication\Services\ChannelService;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Event\PlayerChangedPlaceEvent;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;
    private ChannelService $channelService;

    public function __construct(
        StatusServiceInterface $statusService,
        ChannelService $channelService
    ) {
        $this->statusService = $statusService;
        $this->channelService = $channelService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::CONVERSION_PLAYER => [
                ['onConversionPlayer'],
            ],
            PlayerEvent::NEW_PLAYER => ['onNewPlayer', 100],
            PlayerEvent::DEATH_PLAYER => 'onPlayerDeath',
            PlayerChangedPlaceEvent::class => 'onPlayerChangedPlace',
        ];
    }

    public function onConversionPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();

        $mushStatusConfig = $this->statusService->getStatusConfigByNameAndDaedalus(PlayerStatusEnum::MUSH, $player->getDaedalus());
        $this->statusService->createStatusFromConfig($mushStatusConfig, $player, $playerEvent->getTags(), $playerEvent->getTime());
    }

    public function onNewPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $characterConfig = $playerEvent->getCharacterConfig();
        $reasons = $playerEvent->getTags();
        $time = $playerEvent->getTime();

        if ($characterConfig === null) {
            throw new \LogicException('playerConfig should be provided');
        }
        $initStatuses = $characterConfig->getInitStatuses();

        foreach ($initStatuses as $statusConfig) {
            $this->statusService->createStatusFromConfig(
                $statusConfig,
                $player,
                $reasons,
                $time
            );
        }
    }

    public function onPlayerDeath(PlayerEvent $playerEvent): void
    {
        $this->statusService->removeAllStatuses($playerEvent->getPlayer(), $playerEvent->getTags(), $playerEvent->getTime());
    }

    public function onPlayerChangedPlace(PlayerChangedPlaceEvent $event): void
    {
        $oldPlace = $event->getOldPlace();

        if ($oldPlace->hasStatus(PlaceStatusEnum::CEASEFIRE->toString())) {
            $this->deleteCeasefireStatus($event);
        }
    }

    private function deleteCeasefireStatus(PlayerChangedPlaceEvent $event): void
    {
        $oldPlace = $event->getOldPlace();
        $player = $event->getPlayer();

        $ceasefireStatus = $oldPlace->getStatusByNameOrThrow(PlaceStatusEnum::CEASEFIRE->toString());
        if ($ceasefireStatus->getTargetOrThrow()->notEquals($player)) {
            return;
        }

        $this->statusService->removeStatus(
            statusName: $ceasefireStatus->getName(),
            holder: $ceasefireStatus->getOwner(),
            tags: $event->getTags(),
            time: $event->getTime(),
            visibility: VisibilityEnum::PUBLIC,
        );
    }
}
