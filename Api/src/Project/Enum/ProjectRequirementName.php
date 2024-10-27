<?php

declare(strict_types=1);

namespace Mush\Project\Enum;

enum ProjectRequirementName: string
{
    case CHUN_IN_LABORATORY = 'chun_in_laboratory';
    case MUSH_PLAYER_DEAD = 'mush_player_dead';
    case SOAP_IN_LABORATORY = 'soap_in_laboratory';
    case MUSH_SAMPLE_IN_LABORATORY = 'mush_sample_in_laboratory';
    case MUSH_GENOME_DISK_IN_LABORATORY = 'mush_genome_disk_in_laboratory';
    case BLASTER_IN_LABORATORY = 'blaster_in_laboratory';
    case WATER_STICK_IN_LABORATORY = 'water_stick_in_laboratory';
    case STARMAP_FRAGMENT_IN_LABORATORY = 'starmap_fragment_in_laboratory';
    case MEDIKIT_IN_LABORATORY = 'medikit_in_laboratory';
    case SCHRODINGER_IN_PLAYER_INVENTORY = 'schrodinger_in_player_inventory';
    case FOOD_IN_LABORATORY = 'food_in_laboratory';
}
