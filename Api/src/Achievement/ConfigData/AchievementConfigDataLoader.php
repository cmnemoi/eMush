<?php

declare(strict_types=1);

namespace Mush\Achievement\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Achievement\Entity\AchievementConfig;
use Mush\Achievement\Entity\StatisticConfig;
use Mush\Achievement\Repository\AchievementConfigRepository;
use Mush\Game\ConfigData\ConfigDataLoader;

final class AchievementConfigDataLoader extends ConfigDataLoader
{
    private AchievementConfigRepository $achievementConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        AchievementConfigRepository $achievementConfigRepository,
    ) {
        parent::__construct($entityManager);
        $this->entityManager = $entityManager;
        $this->achievementConfigRepository = $achievementConfigRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (AchievementConfigData::getAll() as $achievementConfigDto) {
            $achievementConfig = $this->achievementConfigRepository->findOneByNameOrNull($achievementConfigDto->name);
            $statisticConfig = $this->entityManager->getRepository(StatisticConfig::class)->findOneByName($achievementConfigDto->statistic);

            if ($achievementConfig === null) {
                $achievementConfig = new AchievementConfig(
                    name: $achievementConfigDto->name,
                    points: $achievementConfigDto->points,
                    unlockThreshold: $achievementConfigDto->threshold,
                    statisticConfig: $statisticConfig,
                );
            } else {
                $achievementConfig->updateFromDto($achievementConfigDto, $statisticConfig);
            }

            $this->entityManager->persist($achievementConfig);
        }
        $this->entityManager->flush();
    }
}
