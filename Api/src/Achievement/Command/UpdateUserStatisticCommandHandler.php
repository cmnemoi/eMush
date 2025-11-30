<?php

declare(strict_types=1);

namespace Mush\Achievement\Command;

use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Enum\StatisticStrategyEnum;
use Mush\Achievement\Event\StatisticIncrementedEvent;
use Mush\Achievement\Repository\StatisticConfigRepositoryInterface;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateUserStatisticCommandHandler
{
    public function __construct(
        private EventServiceInterface $eventService,
        private StatisticConfigRepositoryInterface $statisticConfigRepository,
        private StatisticRepositoryInterface $statisticRepository
    ) {}

    public function __invoke(UpdateUserStatisticCommand $command): void
    {
        if ($command->statisticName === StatisticEnum::NULL || $command->count <= 0) {
            return;
        }

        $config = $this->statisticConfigRepository->findOneByName($command->statisticName);
        $statistic = $this->statisticRepository->findByNameAndUserIdOrNull($command->statisticName, $command->userId);

        if (!$statistic) {
            $statistic = new Statistic(
                config: $config,
                userId: $command->userId,
                count: $command->count
            );
        } else {
            match ($config->getStrategy()) {
                StatisticStrategyEnum::MAX => $statistic->updateIfSuperior($command->count),
                StatisticStrategyEnum::INCREMENT => $statistic->incrementCount($command->count),
                default => throw new \LogicException('Undefined statistic strategy to update.'),
            };
        }

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
}
