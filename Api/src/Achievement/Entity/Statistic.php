<?php

declare(strict_types=1);

namespace Mush\Achievement\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Achievement\ConfigData\StatisticConfigData;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\User\Entity\User;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'unique_statistic_per_user', columns: ['config_id', 'user_id'])]
class Statistic
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

    public function __construct(StatisticConfig $config, int $userId, int $count = 0)
    {
        $this->config = $config;
        $this->userId = $userId;
        $this->count = $count;
    }

    /**
     * Never use this method in production code. It is only used for unit testing.
     */
    public static function createForTest(StatisticEnum $name, int $count = 0, int $userId = 0): self
    {
        $statistic = new self(StatisticConfig::fromDto(StatisticConfigData::getByName($name)), $userId);
        $statistic
            ->withCount($count)
            ->withId(crc32(serialize($statistic)));

        return $statistic;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
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

    public function toArray(): array
    {
        return [
            'name' => $this->config->getName(),
            'count' => $this->count,
            'userId' => $this->userId,
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

    private function withId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    private function withCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }
}
