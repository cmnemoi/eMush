<?php

declare(strict_types=1);

namespace Mush\Achievement\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Achievement\Dto\StatisticConfigDto;
use Mush\Achievement\Enum\StatisticEnum;

#[ORM\Entity]
class StatisticConfig
{
    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 1])]
    #[ORM\Version]
    private int $version = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', enumType: StatisticEnum::class, nullable: false, options: ['default' => StatisticEnum::NULL])]
    private StatisticEnum $name;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isRare;

    public function __construct(StatisticEnum $name, bool $isRare)
    {
        $this->name = $name;
        $this->isRare = $isRare;
    }

    public static function fromDto(StatisticConfigDto $dto): self
    {
        return new self($dto->name, $dto->isRare)->setupId();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): StatisticEnum
    {
        return $this->name;
    }

    public function isRare(): bool
    {
        return $this->isRare;
    }

    public function updateFromDto(StatisticConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->isRare = $dto->isRare;
    }

    public function equals(self $statisticConfig): bool
    {
        return $this->id === $statisticConfig->id;
    }

    private function setupId(): self
    {
        $this->id = crc32(serialize($this));

        return $this;
    }
}
