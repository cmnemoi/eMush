<?php

declare(strict_types=1);

namespace Mush\Achievement\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\User\Entity\User;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'unique_pending_statistic_per_user_and_closed_daedalus', columns: ['config_id', 'user_id', 'closed_daedalus_id'])]
class PendingStatistic
{
    use TimestampableEntity;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 1])]
    #[ORM\Version]
    private int $version = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: StatisticConfig::class)]
    private StatisticConfig $config;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $count;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $userId;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $user;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $closedDaedalusId;

    #[ORM\ManyToOne(targetEntity: ClosedDaedalus::class)]
    private ClosedDaedalus $closedDaedalus;

    public function __construct(StatisticConfig $config, int $userId, int $closedDaedalusId, int $count = 1)
    {
        $this->config = $config;
        $this->userId = $userId;
        $this->closedDaedalusId = $closedDaedalusId;
        $this->count = $count;

        if ($this->count <= 0) {
            throw new \RuntimeException('Statistic count cannot be negative or zero');
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getClosedDaedalusId(): int
    {
        return $this->closedDaedalusId;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getConfig(): StatisticConfig
    {
        return $this->config;
    }

    public function incrementCount(int $amount = 1): void
    {
        $this->count += $amount;
    }

    public function updateIfSuperior(int $newValue): void
    {
        if ($newValue > $this->count) {
            $this->count = $newValue;
        }
    }

    public function toArray(): array
    {
        return [
            'name' => $this->config->getName(),
            'count' => $this->count,
            'userId' => $this->userId,
            'closedDaedalusId' => $this->closedDaedalusId,
            'isRare' => $this->config->isRare(),
        ];
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @deprecated Should be used only in Doctrine repositories. Use getUserId() instead.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setClosedDaedalusId(int $closedDaedalusId): void
    {
        $this->closedDaedalusId = $closedDaedalusId;
    }

    /**
     * @deprecated Should be used only in Doctrine repositories. Use getClosedDaedalusId() instead.
     */
    public function getClosedDaedalus(): ClosedDaedalus
    {
        return $this->closedDaedalus;
    }

    /**
     * @deprecated should be used only in Doctrine repositories
     */
    public function setClosedDaedalus(ClosedDaedalus $closedDaedalus): void
    {
        $this->closedDaedalus = $closedDaedalus;
    }
}
