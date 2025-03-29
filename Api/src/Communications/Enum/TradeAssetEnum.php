<?php

declare(strict_types=1);

namespace Mush\Communications\Enum;

enum TradeAssetEnum: string
{
    case DAEDALUS_VARIABLE = 'daedalus_variable';
    case ITEM = 'item';
    case RANDOM_PLAYER = 'random_player';
    case RANDOM_PROJECT = 'random_project';
    case SPECIFIC_PROJECT = 'specific_project';
    case SPECIFIC_PLAYER = 'specific_player';
    case NULL = '';

    public function toString(): string
    {
        return $this->value;
    }
}
