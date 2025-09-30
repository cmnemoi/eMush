<?php

declare(strict_types=1);

namespace Mush\Achievement\Command;

use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Event\StatisticIncrementedEvent;
use Mush\Achievement\Repository\StatisticConfigRepositoryInterface;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class IncrementUserStatisticCommandHandler
{
    public function __construct(
        private EventServiceInterface $eventService,
        private StatisticConfigRepositoryInterface $statisticConfigRepository,
        private StatisticRepositoryInterface $statisticRepository
    ) {}

    public function __invoke(IncrementUserStatisticCommand $command): void
    {
        if ($command->statisticName === StatisticEnum::NULL) {
            return;
        }

        $statisticName = $command->statisticName;
        $userId = $command->userId;
        $language = $command->language;

        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull($statisticName, $userId)
            ?? new Statistic(
                config: $this->statisticConfigRepository->findOneByName($statisticName),
                userId: $userId
            );
        $statistic->incrementCount();
        $this->statisticRepository->save($statistic);

        $this->eventService->callEvent(new StatisticIncrementedEvent($statistic->getId(), $language), StatisticIncrementedEvent::class);
    }
}
