<?php

declare(strict_types=1);

namespace Mush\Achievement\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Achievement\Dto\AchievementConfigDto;
use Mush\Achievement\Enum\AchievementEnum;

#[ORM\Entity]
class AchievementConfig
{
    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 1])]
    #[ORM\Version]
    private int $version = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', enumType: AchievementEnum::class, nullable: false, options: ['default' => AchievementEnum::NULL])]
    private AchievementEnum $name;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $points;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $unlockThreshold;

    #[ORM\ManyToOne(targetEntity: StatisticConfig::class)]
    private StatisticConfig $statisticConfig;

    public function __construct(AchievementEnum $name, int $points, int $unlockThreshold, StatisticConfig $statisticConfig)
    {
        $this->name = $name;
        $this->points = $points;
        $this->unlockThreshold = $unlockThreshold;
        $this->statisticConfig = $statisticConfig;
    }

    public function getName(): AchievementEnum
    {
        return $this->name;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getUnlockThreshold(): int
    {
        return $this->unlockThreshold;
    }

    public function getStatisticConfig(): StatisticConfig
    {
        return $this->statisticConfig;
    }

    public function shouldUnlockAchievementForStatistic(Statistic $statistic): bool
    {
        return $this->statisticConfig->equals($statistic->getConfig()) && $this->getUnlockThreshold() <= $statistic->getCount();
    }

    public function updateFromDto(AchievementConfigDto $dto, StatisticConfig $statisticConfig): void
    {
        $this->name = $dto->name;
        $this->points = $dto->points;
        $this->unlockThreshold = $dto->threshold;
        $this->statisticConfig = $statisticConfig;
    }
}
