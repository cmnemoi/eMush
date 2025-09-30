<?php

declare(strict_types=1);

namespace Mush\Achievement\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Achievement\Enum\AchievementEnum;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'unique_achievement_per_user', columns: ['config_id', 'statistic_id'])]
class Achievement
{
    use TimestampableEntity;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 1])]
    #[ORM\Version]
    private int $version = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: AchievementConfig::class)]
    private AchievementConfig $config;

    #[ORM\ManyToOne(targetEntity: Statistic::class)]
    private Statistic $statistic;

    private int $statisticId;

    public function __construct(AchievementConfig $config, int $statisticId)
    {
        $this->config = $config;
        $this->statisticId = $statisticId;
    }

    public function getConfig(): AchievementConfig
    {
        return $this->config;
    }

    public function getName(): AchievementEnum
    {
        return $this->config->getName();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->config->getName(),
            'points' => $this->config->getPoints(),
            'unlockThreshold' => $this->config->getUnlockThreshold(),
            'statId' => $this->statisticId,
        ];
    }

    public function getStatisticId(): int
    {
        return $this->statisticId;
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setStatistic(Statistic $statistic): static
    {
        $this->statistic = $statistic;

        return $this;
    }
}
