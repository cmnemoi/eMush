<?php

declare(strict_types=1);

namespace Mush\Achievement\Command;

use Mush\Achievement\Entity\Achievement;
use Mush\Achievement\Event\AchievementUnlockedEvent;
use Mush\Achievement\Repository\AchievementConfigRepositoryInterface;
use Mush\Achievement\Repository\AchievementRepositoryInterface;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UnlockStatisticAchievementCommandHandler
{
    public function __construct(
        private AchievementConfigRepositoryInterface $achievementConfigRepository,
        private AchievementRepositoryInterface $achievementRepository,
        private EventDispatcherInterface $eventDispatcher,
        private StatisticRepositoryInterface $statisticRepository
    ) {}

    public function __invoke(UnlockStatisticAchievementCommand $command): void
    {
        $statisticId = $command->statisticId;

        if ($this->achievementRepository->existsForStatistic($statisticId)) {
            return;
        }

        $statistic = $this->statisticRepository->findOneById($statisticId);
        $achievementConfigsToUnlock = $this->achievementConfigRepository->findAllToUnlockForStatistic($statistic);

        foreach ($achievementConfigsToUnlock as $achievementConfig) {
            $achievement = new Achievement($achievementConfig, $statistic->getId());
            $this->achievementRepository->save($achievement);
            $this->eventDispatcher->dispatch(new AchievementUnlockedEvent($achievement, $statistic->getUserId(), $command->language));
        }
    }
}
