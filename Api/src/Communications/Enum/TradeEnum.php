<?php

declare(strict_types=1);

namespace Mush\Communications\Enum;

enum TradeEnum: string
{
    case FOREST_DEAL = 'forest_deal';
    case PILGREDISSIM = 'pilgredissim';
    case GOOD_PROJECTIONS = 'good_projections';
    case TECHNO_REWRITE = 'techno_rewrite';
    case HUMAN_VS_FUEL = 'human_vs_fuel';
    case HUMAN_VS_OXY = 'human_vs_oxy';
    case HUMAN_VS_TREE = 'human_vs_tree';
    case NULL = '';

    public function toString(): string
    {
        return $this->value;
    }

    public function toImageId(): string
    {
        return match ($this) {
            self::FOREST_DEAL => 'transport_2',
            self::PILGREDISSIM => 'transport_3',
            self::GOOD_PROJECTIONS => 'transport_4',
            self::TECHNO_REWRITE => 'transport_1',
            self::HUMAN_VS_FUEL => 'transport_2',
            self::HUMAN_VS_OXY => 'transport_3',
            self::HUMAN_VS_TREE => 'transport_4',
            self::NULL => '',
            default => '',
        };
    }

    /**
     * @return TradeEnum[]
     */
    public static function getAll(): array
    {
        return [
            self::FOREST_DEAL,
            self::PILGREDISSIM,
            self::GOOD_PROJECTIONS,
            self::TECHNO_REWRITE,
            self::HUMAN_VS_FUEL,
            self::HUMAN_VS_OXY,
            self::HUMAN_VS_TREE,
        ];
    }
}
