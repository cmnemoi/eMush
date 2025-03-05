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
}
