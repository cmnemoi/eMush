<?php

namespace Mush\Disease\Enum;

enum DiseaseEventEnum: string
{
    case DROP_HEAVY_ITEMS = 'disease_drop_heavy_items';
    case ADD_CRITICAL_HAEMORRHAGE_100 = 'disease_add_critical_heamorrhage_100';
    case ADD_HAEMORRHAGE_20 = 'disease_add_heamorrhage_20';
    case DEAL_6_DMG_ADD_BURN = 'deal_6_dmg_add_burn';
    case NONE = 'none';

    public function toConfigKey(string $configKey): string
    {
        return $this->value . '_' . $configKey;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
