<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Communications\Enum\TradeAssetEnum;

#[ORM\Entity]
class TradeAsset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', enumType: TradeAssetEnum::class, nullable: false, options: ['default' => TradeAssetEnum::NULL])]
    private TradeAssetEnum $type = TradeAssetEnum::NULL;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    private string $assetName = '';

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $quantity = 0;

    public function __construct(TradeAssetEnum $type, int $quantity, string $assetName = '')
    {
        $this->type = $type;
        $this->quantity = $quantity;
        $this->assetName = $assetName;
    }

    public function getType(): TradeAssetEnum
    {
        return $this->type;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTranslationKey(): string
    {
        return $this->assetName ? $this->assetName : $this->type->toString();
    }
}
