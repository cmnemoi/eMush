<?php

namespace Mush\Status\Listener;

use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private StatusServiceInterface $statusService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StatusServiceInterface $statusService,
        PlayerDiseaseServiceInterface $playerDiseaseService,
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->statusService = $statusService;
        $this->playerDiseaseService = $playerDiseaseService;
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
        $mushStatus = $this->statusService->createStatusFromConfig($mushStatusConfig, $player, $playerEvent->getReason(), $playerEvent->getTime());
        $this->statusService->persist($mushStatus);
    }

    public function onNewPlayer(PlayerEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $characterConfig = $playerEvent->getCharacterConfig();
        $reason = $playerEvent->getReason();
        $time = $playerEvent->getTime();

        if ($characterConfig === null) {
            throw new \LogicException('playerConfig should be provided');
        }
        $initStatuses = $characterConfig->getInitStatuses();

        foreach ($initStatuses as $statusConfig) {
            $this->statusService->createStatusFromConfig(
                $statusConfig,
                $player,
                $reason,
                $time
            );
        }
    }

    public function onPlayerDeath(PlayerEvent $playerEvent): void
    {
        $this->statusService->removeAllStatuses($playerEvent->getPlayer(), $playerEvent->getReason(), $playerEvent->getTime());
    }
}
