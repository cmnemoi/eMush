<?php

declare(strict_types=1);

namespace Mush\Achievement\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Achievement\ConfigData\StatisticConfigData;
use Mush\Achievement\Dto\StatisticConfigDto;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Enum\StatisticStrategyEnum;

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

    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: StatisticStrategyEnum::class, options: ['default' => StatisticStrategyEnum::NULL])]
    private StatisticStrategyEnum $strategy;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isRare;

    public function __construct(StatisticEnum $name, StatisticStrategyEnum $strategy, bool $isRare)
    {
        $this->name = $name;
        $this->strategy = $strategy;
        $this->isRare = $isRare;
    }

    public static function fromDto(StatisticConfigDto $dto): self
    {
        return new self($dto->name, $dto->strategy, $dto->isRare)->setupId();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): StatisticEnum
    {
        return $this->name;
    }

    public function getStrategy(): StatisticStrategyEnum
    {
        if ($this->strategy === StatisticStrategyEnum::NULL) {
            $this->updateFromDto(StatisticConfigData::getByName($this->name));
        }

        if ($this->strategy === StatisticStrategyEnum::NULL) {
            throw new \Exception("Got null strategy for {$this->name->value}.");
        }

        return $this->strategy;
    }

    public function isRare(): bool
    {
        return $this->isRare;
    }

    public function updateFromDto(StatisticConfigDto $dto): void
    {
        $this->name = $dto->name;
        $this->strategy = $dto->strategy;
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
