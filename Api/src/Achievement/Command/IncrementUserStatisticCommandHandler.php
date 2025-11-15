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
        if ($command->statisticName === StatisticEnum::NULL || $command->increment <= 0) {
            return;
        }

        $statistic = $this->incrementOrCreateStatistic($command);
        $this->statisticRepository->save($statistic);
        $this->eventService->callEvent(
            new StatisticIncrementedEvent(
                $statistic->getId(),
                $statistic->getUserId(),
                $command->language
            ),
            StatisticIncrementedEvent::class
        );
    }

    private function incrementOrCreateStatistic(IncrementUserStatisticCommand $command): Statistic
    {
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull($command->statisticName, $command->userId);
        if ($statistic) {
            $statistic->incrementCount($command->increment);

            return $statistic;
        }

        return new Statistic(
            config: $this->statisticConfigRepository->findOneByName($command->statisticName),
            userId: $command->userId,
            count: $command->increment,
        );
    }
}
