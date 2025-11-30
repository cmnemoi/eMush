<?php

declare(strict_types=1);

namespace Mush\Achievement\Listener;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Services\UpdatePlayerStatisticService;
use Mush\Equipment\Entity\Door;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class StatusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private UpdatePlayerStatisticService $updatePlayerStatisticService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => ['onStatusApplied', EventPriorityEnum::LOWEST],
            StatusEvent::STATUS_REMOVED => ['onStatusRemoved', EventPriorityEnum::LOWEST],
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $this->incrementStatsFromDaedalusStatuses($event);
        $this->incrementStatsFromPlayerStatuses($event);
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $author = $event->getAuthor();
        if (!$author) {
            return;
        }

        $statisticName = match ($event->getStatusName()) {
            StatusEnum::FIRE => StatisticEnum::EXTINGUISH_FIRE,
            EquipmentStatusEnum::BROKEN => $this->getDoorRepairedStatisticIfDoor($event),
            default => StatisticEnum::NULL,
        };

        $this->updatePlayerStatisticService->execute(
            player: $author,
            statisticName: $statisticName,
        );
    }

    private function incrementStatsFromDaedalusStatuses(StatusEvent $event): void
    {
        if ($event->getStatusName() === DaedalusStatusEnum::COMMUNICATIONS_EXPERT) {
            foreach ($event->getDaedalus()->getAlivePlayers() as $player) {
                $this->updatePlayerStatisticService->execute(
                    player: $player,
                    statisticName: StatisticEnum::COMMUNICATION_EXPERT,
                );
            }
        }
    }

    private function incrementStatsFromPlayerStatuses(StatusEvent $event): void
    {
        $player = $event->getStatusHolder();
        if (!$player instanceof Player) {
            return;
        }

        $statisticName = match ($event->getStatusName()) {
            PlayerStatusEnum::GAGGED => StatisticEnum::GAGGED,
            PlayerStatusEnum::HAS_PETTED_CAT => StatisticEnum::CAT_CUDDLED,
            default => StatisticEnum::NULL,
        };

        $this->updatePlayerStatisticService->execute(
            player: $player,
            statisticName: $statisticName,
        );
    }

    private function getDoorRepairedStatisticIfDoor(StatusEvent $event): StatisticEnum
    {
        $statusHolder = $event->getStatusHolder();

        return $statusHolder instanceof Door ? StatisticEnum::DOOR_REPAIRED : StatisticEnum::NULL;
    }
}
