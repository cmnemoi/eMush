<?php

declare(strict_types=1);

namespace Mush\Triumph\ConfigData;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Triumph\Dto\TriumphConfigDto;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphScope;

abstract class TriumphConfigData
{
    /**
     * @var array<TriumphConfigDto>
     */
    public static function getAll(): array
    {
        return [
            new TriumphConfigDto(
                key: TriumphEnum::CYCLE_HUMAN->toConfigKey('default'),
                name: TriumphEnum::CYCLE_HUMAN,
                targetedEvent: DaedalusCycleEvent::DAEDALUS_NEW_CYCLE,
                scope: TriumphScope::HUMAN_TARGET,
                quantity: 1,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::CYCLE_MUSH->toConfigKey('default'),
                name: TriumphEnum::CYCLE_MUSH,
                targetedEvent: PlayerCycleEvent::PLAYER_NEW_CYCLE,
                scope: TriumphScope::MUSH_TARGET,
                quantity: -2,
            ),
        ];
    }

    public static function getByName(string $name): TriumphConfigDto
    {
        return array_filter(self::getAll(), static fn (TriumphConfigDto $config) => $config->name === $name)[0];
    }
}
