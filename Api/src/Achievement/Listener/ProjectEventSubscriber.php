<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        $statisticName = match ($event->getProject()->getName()) {
            ProjectName::PILGRED->toString() => StatisticEnum::PILGRED_IS_BACK,
            ProjectName::PLASMA_SHIELD->toString() => StatisticEnum::PLASMA_SHIELD,
            default => StatisticEnum::NULL,
        };

        foreach ($event->getDaedalus()->getAlivePlayers() as $player) {
            $this->updatePlayerStatisticService->execute(
                player: $player,
                statisticName: $statisticName,
            );
        }
    }
}
