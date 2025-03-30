<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Communications\ConfigData\TradeOptionConfigData;
use Mush\Communications\Dto\TradeConfigDto;
use Mush\Communications\Enum\TradeEnum;

#[ORM\Entity]
#[ORM\Table(name: 'trade_config')]
class TradeConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $key;

    #[ORM\Column(type: 'string', enumType: TradeEnum::class)]
    private TradeEnum $name;

    #[ORM\OneToMany(mappedBy: 'tradeConfig', targetEntity: TradeOptionConfig::class, cascade: ['persist'])]
    private Collection $tradeOptionConfigs;

    public function __construct(
        string $key,
        TradeEnum $name,
        array $tradeOptionConfigs = []
    ) {
        $this->key = $key;
        $this->name = $name;
        $this->tradeOptionConfigs = new ArrayCollection([]);
        $this->setTradeOptionConfigs($tradeOptionConfigs);
    }

    public static function fromDto(TradeConfigDto $tradeConfigDto): self
    {
        return new self(
            key: $tradeConfigDto->key,
            name: $tradeConfigDto->name,
            tradeOptionConfigs: array_map(
                static fn (string $tradeOptionName) => TradeOptionConfig::fromDto(TradeOptionConfigData::getByName($tradeOptionName)),
                $tradeConfigDto->tradeOptions,
            ),
        );
    }

    public function getName(): TradeEnum
    {
        return $this->name;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return Collection<int, TradeOptionConfig>
     */
    public function getTradeOptionConfigs(): Collection
    {
        return $this->tradeOptionConfigs;
    }

    public function update(self $tradeConfig): void
    {
        $this->key = $tradeConfig->key;
        $this->name = $tradeConfig->name;
        $this->setTradeOptionConfigs($tradeConfig->tradeOptionConfigs->toArray());
    }

    /**
     * @param TradeOptionConfig[] $tradeOptionConfigs
     */
    private function setTradeOptionConfigs(array $tradeOptionConfigs): void
    {
        foreach ($tradeOptionConfigs as $tradeOptionConfig) {
            if ($this->tradeOptionConfigs->contains($tradeOptionConfig)) {
                continue;
            }

            $tradeOptionConfig->setTradeConfig($this);
            $this->tradeOptionConfigs->add($tradeOptionConfig);
        }
    }
}
