<?php

declare(strict_types=1);

namespace Mush\Exploration\Event;

/** @codeCoverageIgnore */
class PlanetSectorEvent
{
    public const ACCIDENT = 'accident';
    public const AGAIN = 'again';
    public const ARTEFACT = 'artefact';
    public const BACK = 'back';
    public const DISASTER = 'disaster';
    public const DISEASE = 'disease';
    public const FIGHT = 'fight';
    public const FIND_LOST = 'find_lost';
    public const FUEL = 'fuel';
    public const HARVEST = 'harvest';
    public const ITEM_LOST = 'item_lost';
    public const KILL_ALL = 'kill_all';
    public const KILL_LOST = 'kill_lost';
    public const KILL_RANDOM = 'kill_random';
    public const MUSH_TRAP = 'mush_trap';
    public const PLAYER_LOST = 'player_lost';
    public const NOTHING_TO_REPORT = 'nothing_to_report';
    public const OXYGEN = 'oxygen';
    public const PROVISION = 'provision';
    public const STARMAP = 'starmap';
    public const TIRED = 'tired';
}
