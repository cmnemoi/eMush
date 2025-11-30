<?php

declare(strict_types=1);

namespace Mush\Achievement\Services;

use Mush\Achievement\Command\UpdateUserStatisticCommand;
use Mush\Achievement\Entity\PendingStatistic;
use Mush\Achievement\Entity\StatisticConfig;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Enum\StatisticStrategyEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Achievement\Repository\StatisticConfigRepositoryInterface;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class UpdatePlayerStatisticService
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private PendingStatisticRepositoryInterface $pendingStatisticRepository,
        private StatisticConfigRepositoryInterface $statisticConfigRepository,
        private StatisticRepositoryInterface $statisticRepository,
    ) {}

    public function execute(Player $player, StatisticEnum $statisticName, int $count = 1): void
    {
        if ($statisticName === StatisticEnum::NULL || $count <= 0) {
            return;
        }

        $config = $this->statisticConfigRepository->findOneByName($statisticName);
        $strategy = $config->getStrategy();
        $user = $player->getUser();
        if ($strategy === StatisticStrategyEnum::MAX && $this->doesUserHaveStatisticOfMinimumValue($user, $statisticName, $count)) {
            return;
        }

        $daedalusInfo = $player->getDaedalus()->getDaedalusInfo();

        if ($daedalusInfo->isDaedalusFinished()) {
            $this->commandBus->dispatch(
                new UpdateUserStatisticCommand(
                    userId: $user->getId(),
                    statisticName: $statisticName,
                    language: $player->getLanguage(),
                    count: $count
                )
            );
        } else {
            $pendingStatistic = $this->updatePendingStatistic($user->getId(), $daedalusInfo->getClosedDaedalus()->getId(), $config, $count);
            $this->pendingStatisticRepository->save($pendingStatistic);
        }
    }

    private function updatePendingStatistic(
        int $userId,
        int $closedDaedalusId,
        StatisticConfig $statisticConfig,
        int $count
    ): PendingStatistic {
        $pendingStatistic = $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
            name: $statisticConfig->getName(),
            userId: $userId,
            closedDaedalusId: $closedDaedalusId
        );

        if (!$pendingStatistic) {
            return new PendingStatistic(
                config: $statisticConfig,
                userId: $userId,
                closedDaedalusId: $closedDaedalusId,
                count: $count
            );
        }

        match ($statisticConfig->getStrategy()) {
            StatisticStrategyEnum::MAX => $pendingStatistic->updateIfSuperior($count),
            StatisticStrategyEnum::INCREMENT => $pendingStatistic->incrementCount($count),
            default => throw new \LogicException('Undefined strategy for pending statistic.'),
        };

        return $pendingStatistic;
    }

    private function doesUserHaveStatisticOfMinimumValue(User $user, StatisticEnum $statisticName, int $minimumCount): bool
    {
        $userStatistic = $this->statisticRepository->findByNameAndUserIdOrNull($statisticName, $user->getId());
        if (!$userStatistic) {
            return false;
        }

        return $userStatistic->getCount() >= $minimumCount;
    }
}
