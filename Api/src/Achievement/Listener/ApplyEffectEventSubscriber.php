<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Action\Event\ApplyEffectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ApplyEffectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ApplyEffectEvent::REPORT_EQUIPMENT => 'onReportEquipment',
            ApplyEffectEvent::REPORT_FIRE => 'onReportFire',
        ];
    }

    public function onReportEquipment(ApplyEffectEvent $event): void
    {
        $player = $event->getAuthor();

        $this->updatePlayerStatisticService->execute(
            player: $player,
            statisticName: StatisticEnum::SIGNAL_EQUIP,
        );
    }

    public function onReportFire(ApplyEffectEvent $event): void
    {
        $player = $event->getAuthor();

        $this->updatePlayerStatisticService->execute(
            player: $player,
            statisticName: StatisticEnum::SIGNAL_FIRE,
        );
    }
}
