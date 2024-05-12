<?php

declare(strict_types=1);

namespace Mush\Action\Enum;

/**
 * Class enumerating the Action holder
 * Action Holder determine in which normalizer the action is going to appear
 * PLAYER: The action is always available for the player (CurrentPlayerNormalizer)
 * OTHER_PLAYER: The action is displayed when targeting another player (OtherPlayerNormalizer)
 * EQUIPMENT: The action is displayed when targeting an equipment (EquipmentNormalizer)
 * HUNTER: The action is displayed when targeting a hunter (HunterNormalizer)
 * TERMINAL: The action is displayed when the player is focussed on a terminal (TerminalNormalizer)
 * PLANET: The action is displayed when targeting a planet (PlanetNormalizer)
 * PROJECT: The action is displayed when targeting a project (ProjectNormalizer).
 */
enum ActionHolderEnum: string
{
    case PLAYER = 'player';
    case OTHER_PLAYER = 'other_player';
    case EQUIPMENT = 'equipment';
    case HUNTER = 'hunter';
    case TERMINAL = 'terminal';
    case PLANET = 'planet';
    case PROJECT = 'project';
    case NULL = '';

    // fix migration, to delete just after !
    case SELF = 'self';
    case ROOM = 'room';
    case CURRENT = 'current';
}
