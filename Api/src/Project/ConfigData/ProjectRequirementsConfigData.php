<?php

declare(strict_types=1);

namespace Mush\Project\ConfigData;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Project\Enum\ProjectRequirementName;
use Mush\Project\Enum\ProjectRequirementType;

/**
 * @codeCoverageIgnore
 */
abstract class ProjectRequirementsConfigData
{
    public static function getAll(): array
    {
        return [
            [
                'name' => ProjectRequirementName::CHUN_IN_LABORATORY,
                'type' => ProjectRequirementType::CHUN_IN_LABORATORY,
                'target' => EquipmentEnum::null,
            ],
            [
                'name' => ProjectRequirementName::MUSH_PLAYER_DEAD,
                'type' => ProjectRequirementType::MUSH_PLAYER_DEAD,
                'target' => EquipmentEnum::null,
            ],
            [
                'name' => ProjectRequirementName::MUSH_SAMPLE_IN_LABORATORY,
                'type' => ProjectRequirementType::ITEM_IN_LABORATORY,
                'target' => ItemEnum::MUSH_SAMPLE,
            ],
            [
                'name' => ProjectRequirementName::SOAP_IN_LABORATORY,
                'type' => ProjectRequirementType::ITEM_IN_LABORATORY,
                'target' => GearItemEnum::SOAP,
            ],
            [
                'name' => ProjectRequirementName::MUSH_GENOME_DISK_IN_LABORATORY,
                'type' => ProjectRequirementType::ITEM_IN_LABORATORY,
                'target' => ItemEnum::MUSH_GENOME_DISK,
            ],
            [
                'name' => ProjectRequirementName::BLASTER_IN_LABORATORY,
                'type' => ProjectRequirementType::ITEM_IN_LABORATORY,
                'target' => ItemEnum::BLASTER,
            ],
            [
                'name' => ProjectRequirementName::WATER_STICK_IN_LABORATORY,
                'type' => ProjectRequirementType::ITEM_IN_LABORATORY,
                'target' => ItemEnum::WATER_STICK,
            ],
            [
                'name' => ProjectRequirementName::STARMAP_FRAGMENT_IN_LABORATORY,
                'type' => ProjectRequirementType::ITEM_IN_LABORATORY,
                'target' => ItemEnum::STARMAP_FRAGMENT,
            ],
            [
                'name' => ProjectRequirementName::MEDIKIT_IN_LABORATORY,
                'type' => ProjectRequirementType::ITEM_IN_LABORATORY,
                'target' => ToolItemEnum::MEDIKIT,
            ],
            [
                'name' => ProjectRequirementName::SCHRODINGER_IN_PLAYER_INVENTORY,
                'type' => ProjectRequirementType::ITEM_IN_PLAYER_INVENTORY,
                'target' => ItemEnum::SCHRODINGER,
            ],
        ];
    }
}
