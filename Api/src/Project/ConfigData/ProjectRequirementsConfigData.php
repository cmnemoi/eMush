<?php

declare(strict_types=1);

namespace Mush\Project\ConfigData;

use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Project\Dto\ProjectRequirementConfigDto;
use Mush\Project\Enum\ProjectRequirementName;
use Mush\Project\Enum\ProjectRequirementType;

/**
 * @codeCoverageIgnore
 */
abstract class ProjectRequirementsConfigData
{
    /**
     * @return ProjectRequirementConfigDto[]
     */
    public static function getAll(): array
    {
        return [
            new ProjectRequirementConfigDto(
                ProjectRequirementName::CHUN_IN_LABORATORY,
                ProjectRequirementType::CHUN_IN_LABORATORY
            ),
            new ProjectRequirementConfigDto(
                ProjectRequirementName::MUSH_PLAYER_DEAD,
                ProjectRequirementType::MUSH_PLAYER_DEAD
            ),
            new ProjectRequirementConfigDto(
                ProjectRequirementName::MUSH_SAMPLE_IN_LABORATORY,
                ProjectRequirementType::ITEM_IN_LABORATORY,
                ItemEnum::MUSH_SAMPLE
            ),
            new ProjectRequirementConfigDto(
                ProjectRequirementName::SOAP_IN_LABORATORY,
                ProjectRequirementType::ITEM_IN_LABORATORY,
                GearItemEnum::SOAP
            ),
            new ProjectRequirementConfigDto(
                ProjectRequirementName::MUSH_GENOME_DISK_IN_LABORATORY,
                ProjectRequirementType::ITEM_IN_LABORATORY,
                ItemEnum::MUSH_GENOME_DISK
            ),
            new ProjectRequirementConfigDto(
                ProjectRequirementName::BLASTER_IN_LABORATORY,
                ProjectRequirementType::ITEM_IN_LABORATORY,
                ItemEnum::BLASTER
            ),
            new ProjectRequirementConfigDto(
                ProjectRequirementName::WATER_STICK_IN_LABORATORY,
                ProjectRequirementType::ITEM_IN_LABORATORY,
                ItemEnum::WATER_STICK
            ),
            new ProjectRequirementConfigDto(
                ProjectRequirementName::STARMAP_FRAGMENT_IN_LABORATORY,
                ProjectRequirementType::ITEM_IN_LABORATORY,
                ItemEnum::STARMAP_FRAGMENT
            ),
            new ProjectRequirementConfigDto(
                ProjectRequirementName::MEDIKIT_IN_LABORATORY,
                ProjectRequirementType::ITEM_IN_LABORATORY,
                ToolItemEnum::MEDIKIT
            ),
            new ProjectRequirementConfigDto(
                ProjectRequirementName::SCHRODINGER_IN_PLAYER_INVENTORY,
                ProjectRequirementType::ITEM_IN_PLAYER_INVENTORY,
                ItemEnum::SCHRODINGER
            ),
        ];
    }
}
