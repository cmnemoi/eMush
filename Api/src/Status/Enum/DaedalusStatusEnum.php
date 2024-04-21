<?php

declare(strict_types=1);

namespace Mush\Status\Enum;

abstract class DaedalusStatusEnum
{
    public const string ASTRONAVIGATION_NERON_CPU_PRIORITY = 'astronavigation_neron_cpu_priority';
    public const string DEFENCE_NERON_CPU_PRIORITY = 'defence_neron_cpu_priority';
    public const string PILGRED_NERON_CPU_PRIORITY = 'pilgred_neron_cpu_priority';
    public const string PROJECTS_NERON_CPU_PRIORITY = 'projects_neron_cpu_priority';
    public const string EXPLORATION_FUEL = 'exploration_fuel';
    public const string EXPLORATION_OXYGEN = 'exploration_oxygen';
    public const string FOLLOWING_HUNTERS = 'following_hunters';
    public const string IN_ORBIT = 'in_orbit';
    public const string NO_GRAVITY = 'no_gravity';
    public const string NO_GRAVITY_REPAIRED = 'no_gravity_repaired';
    public const string TRAVELING = 'traveling';
}
