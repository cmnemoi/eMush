<?php

declare(strict_types=1);

namespace Mush\Project\ConfigData;

use Mush\Equipment\Enum\GameRationEnum;
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
                name: ProjectRequirementName::CHUN_IN_LABORATORY,
                type: ProjectRequirementType::CHUN_IN_LABORATORY
            ),
            new ProjectRequirementConfigDto(
                name: ProjectRequirementName::MUSH_PLAYER_DEAD,
                type: ProjectRequirementType::MUSH_PLAYER_DEAD
            ),
            new ProjectRequirementConfigDto(
                name: ProjectRequirementName::MUSH_SAMPLE_IN_LABORATORY,
                type: ProjectRequirementType::MUSH_SAMPLE_IN_LABORATORY,
                target: ItemEnum::MUSH_SAMPLE
            ),
            new ProjectRequirementConfigDto(
                name: ProjectRequirementName::SOAP_IN_LABORATORY,
                type: ProjectRequirementType::ITEM_IN_LABORATORY,
                target: GearItemEnum::SOAP
            ),
            new ProjectRequirementConfigDto(
                name: ProjectRequirementName::MUSH_GENOME_DISK_IN_LABORATORY,
                type: ProjectRequirementType::ITEM_IN_LABORATORY,
                target: ItemEnum::MUSH_GENOME_DISK
            ),
            new ProjectRequirementConfigDto(
                name: ProjectRequirementName::BLASTER_IN_LABORATORY,
                type: ProjectRequirementType::ITEM_IN_LABORATORY,
                target: ItemEnum::BLASTER
            ),
            new ProjectRequirementConfigDto(
                name: ProjectRequirementName::WATER_STICK_IN_LABORATORY,
                type: ProjectRequirementType::ITEM_IN_LABORATORY,
                target: ItemEnum::WATER_STICK
            ),
            new ProjectRequirementConfigDto(
                name: ProjectRequirementName::STARMAP_FRAGMENT_IN_LABORATORY,
                type: ProjectRequirementType::ITEM_IN_LABORATORY,
                target: ItemEnum::STARMAP_FRAGMENT
            ),
            new ProjectRequirementConfigDto(
                name: ProjectRequirementName::MEDIKIT_IN_LABORATORY,
                type: ProjectRequirementType::ITEM_IN_LABORATORY,
                target: ToolItemEnum::MEDIKIT
            ),
            new ProjectRequirementConfigDto(
                name: ProjectRequirementName::SCHRODINGER_IN_PLAYER_INVENTORY,
                type: ProjectRequirementType::ITEM_IN_PLAYER_INVENTORY,
                target: ItemEnum::SCHRODINGER
            ),
            new ProjectRequirementConfigDto(
                name: ProjectRequirementName::FOOD_IN_LABORATORY,
                type: ProjectRequirementType::FOOD_IN_LABORATORY,
            ),
            new ProjectRequirementConfigDto(
                name: ProjectRequirementName::COFFEE_IN_LABORATORY,
                type: ProjectRequirementType::ITEM_IN_LABORATORY,
                target: GameRationEnum::COFFEE,
            ),
            new ProjectRequirementConfigDto(
                name: ProjectRequirementName::GAME_STARTED,
                type: ProjectRequirementType::GAME_STARTED,
            ),
        ];
    }
}
