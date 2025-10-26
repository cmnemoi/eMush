<?php

declare(strict_types=1);

namespace Mush\Achievement\Command;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Event\StatisticIncrementedEvent;
use Mush\Achievement\Repository\StatisticConfigRepositoryInterface;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateUserStatisticIfSuperiorCommandHandler
{
    public function __construct(
        private EventServiceInterface $eventService,
        private StatisticConfigRepositoryInterface $statisticConfigRepository,
        private StatisticRepositoryInterface $statisticRepository
    ) {}

    public function __invoke(UpdateUserStatisticIfSuperiorCommand $command): void
    {
        if ($command->statisticName === StatisticEnum::NULL) {
            return;
        }

        $statistic = $this->statisticRepository->findOrCreateByNameAndUserId($command->statisticName, $command->userId);
        $statistic->updateIfSuperior($command->newValue);
        $this->statisticRepository->save($statistic);

        $this->eventService->callEvent(new StatisticIncrementedEvent($statistic->getId(), $command->language), StatisticIncrementedEvent::class);
    }
}
