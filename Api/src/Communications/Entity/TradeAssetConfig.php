<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Communications\Enum\TradeAssetEnum;

#[ORM\Entity]
#[ORM\Table(name: 'trade_asset_config')]
class TradeAssetConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $name;

    #[ORM\Column(type: 'string', enumType: TradeAssetEnum::class, nullable: false, options: ['default' => TradeAssetEnum::NULL])]
    private TradeAssetEnum $type;

    #[ORM\Column(type: 'integer')]
    private int $minQuantity;

    #[ORM\Column(type: 'integer')]
    private int $maxQuantity;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $assetName;

    #[ORM\ManyToOne(targetEntity: TradeOptionConfig::class, inversedBy: 'requiredAssetConfigs')]
    private ?TradeOptionConfig $tradeOptionConfigRequired = null;

    #[ORM\ManyToOne(targetEntity: TradeOptionConfig::class, inversedBy: 'offeredAssetConfigs')]
    private ?TradeOptionConfig $tradeOptionConfigOffered = null;

    public function __construct(
        TradeAssetEnum $type,
        int $minQuantity,
        int $maxQuantity,
        string $assetName = '',
        string $name = ''
    ) {
        $this->type = $type;
        $this->minQuantity = $minQuantity;
        $this->maxQuantity = $maxQuantity;
        $this->assetName = $assetName;
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setRequiredTradeOptionConfig(?TradeOptionConfig $tradeOptionConfig): self
    {
        $this->tradeOptionConfigRequired = $tradeOptionConfig;

        return $this;
    }

    public function setOfferedTradeOptionConfig(?TradeOptionConfig $tradeOptionConfig): self
    {
        $this->tradeOptionConfigOffered = $tradeOptionConfig;

        return $this;
    }

    public function getType(): TradeAssetEnum
    {
        return $this->type;
    }

    public function getMinQuantity(): int
    {
        return $this->minQuantity;
    }

    public function getMaxQuantity(): int
    {
        return $this->maxQuantity;
    }

    public function getAssetName(): ?string
    {
        return $this->assetName;
    }

    public function update(self $tradeAssetConfig): self
    {
        $this->name = $tradeAssetConfig->name;
        $this->type = $tradeAssetConfig->type;
        $this->minQuantity = $tradeAssetConfig->minQuantity;
        $this->maxQuantity = $tradeAssetConfig->maxQuantity;
        $this->assetName = $tradeAssetConfig->assetName;

        return $this;
    }
}
