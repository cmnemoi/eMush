<?php

namespace Mush\Status\Listener;

use Mush\Communication\Services\ChannelService;
use Mush\Player\Event\PlayerEvent;
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
}
