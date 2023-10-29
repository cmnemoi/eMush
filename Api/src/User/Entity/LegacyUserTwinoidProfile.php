<?php

declare(strict_types=1);

namespace Mush\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class LegacyUserTwinoidProfile
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\GeneratedValue]
    private int $id = 0;

    #[ORM\OneToOne(targetEntity: LegacyUser::class, inversedBy: 'twinoidProfile')]
    private LegacyUser $legacyUser;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $twinoidId = 0;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $twinoidUsername = '';

    #[ORM\Column(type: 'array', nullable: false)]
    private array $stats = [];

    #[ORM\Column(type: 'array', nullable: false)]
    private array $achievements = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getLegacyUser(): LegacyUser
    {
        return $this->legacyUser;
    }

    public function setLegacyUser(LegacyUser $legacyUser): void
    {
        $this->legacyUser = $legacyUser;
    }

    public function getTwinoidId(): int
    {
        return $this->twinoidId;
    }

    public function setTwinoidId(int $twinoidId): void
    {
        $this->twinoidId = $twinoidId;
    }

    public function getTwinoidUsername(): string
    {
        return $this->twinoidUsername;
    }

    public function setTwinoidUsername(string $twinoidUsername): void
    {
        $this->twinoidUsername = $twinoidUsername;
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    public function setStats(array $stats): void
    {
        $this->stats = $stats;
    }

    public function getAchievements(): array
    {
        return $this->achievements;
    }

    public function setAchievements(array $achievements): void
    {
        $this->achievements = $achievements;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'twinoidId' => $this->getTwinoidId(),
            'twinoidUsername' => $this->getTwinoidUsername(),
            'stats' => $this->getStats(),
            'achievements' => $this->getAchievements(),
        ];
    }
}
