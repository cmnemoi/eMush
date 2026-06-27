<?php

declare(strict_types=1);

namespace Mush\Modifier\Enum;

abstract class ModifierStrategyEnum
{
    public const string ADD_EVENT = 'add_event';
    public const string VARIABLE_MODIFIER = 'variable_modifier';
    public const string MESSAGE_MODIFIER = 'message_modifier';
    public const string PREVENT_EVENT = 'prevent_event';
    public const string SYMPTOM_MODIFIER = 'symptom_modifier';
    public const string DIRECT_MODIFIER = 'direct_modifier';
    public const string EXPLORATION_SECTOR_SELECTION_MODIFIER = 'exploration_sector_selection_modifier';
}
