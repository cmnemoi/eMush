<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
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

    #[ORM\OneToMany(mappedBy: 'tradeConfig', targetEntity: TradeOptionConfig::class, cascade: ['persist', 'remove'])]
    private Collection $tradeOptionConfigs;

    public function __construct(
        string $key,
        TradeEnum $name,
        array $tradeOptionConfigs = []
    ) {
        $this->key = $key;
        $this->name = $name;
        $this->tradeOptionConfigs = new ArrayCollection();

        foreach ($tradeOptionConfigs as $tradeOptionConfig) {
            $this->addTradeOptionConfig($tradeOptionConfig);
        }
    }

    public function getName(): TradeEnum
    {
        return $this->name;
    }

    /**
     * @return Collection<int, TradeOptionConfig>
     */
    public function getTradeOptionConfigs(): Collection
    {
        return $this->tradeOptionConfigs;
    }

    public function addTradeOptionConfig(TradeOptionConfig $tradeOptionConfig): self
    {
        if (!$this->tradeOptionConfigs->contains($tradeOptionConfig)) {
            $this->tradeOptionConfigs->add($tradeOptionConfig);
            $tradeOptionConfig->setTradeConfig($this);
        }

        return $this;
    }

    public function removeTradeOptionConfig(TradeOptionConfig $tradeOptionConfig): self
    {
        $this->tradeOptionConfigs->removeElement($tradeOptionConfig);

        return $this;
    }

    public function update(self $tradeConfig): self
    {
        $this->name = $tradeConfig->name;

        foreach ($this->tradeOptionConfigs as $tradeOptionConfig) {
            $this->removeTradeOptionConfig($tradeOptionConfig);
        }

        foreach ($tradeConfig->getTradeOptionConfigs() as $tradeOptionConfig) {
            $this->addTradeOptionConfig($tradeOptionConfig);
        }

        return $this;
    }
}
