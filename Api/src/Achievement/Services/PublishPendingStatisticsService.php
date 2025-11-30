<?php

declare(strict_types=1);

namespace Mush\Achievement\Services;

use Mush\Achievement\Command\UpdateUserStatisticCommand;
use Mush\Achievement\Entity\PendingStatistic;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Daedalus\Repository\ClosedDaedalusRepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class PublishPendingStatisticsService
{
    public function __construct(
        private ClosedDaedalusRepositoryInterface $closedDaedalusRepository,
        private MessageBusInterface $commandBus,
        private PendingStatisticRepositoryInterface $pendingStatisticRepository,
    ) {}

    public function fromClosedDaedalus(int $closedDaedalusId): void
    {
        $pendingStatistics = $this->pendingStatisticRepository->findAllByClosedDaedalusId($closedDaedalusId);
        $language = $this->closedDaedalusRepository->findOneByIdOrThrow($closedDaedalusId)->getLanguage();

        /** @var PendingStatistic $pendingStatistic */
        foreach ($pendingStatistics as $pendingStatistic) {
            $this->commandBus->dispatch(
                new UpdateUserStatisticCommand(
                    userId: $pendingStatistic->getUserId(),
                    statisticName: $pendingStatistic->getConfig()->getName(),
                    language: $language,
                    count: $pendingStatistic->getCount(),
                )
            );
            $this->pendingStatisticRepository->delete($pendingStatistic);
        }
    }
}
