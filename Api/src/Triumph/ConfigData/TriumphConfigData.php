<?php

declare(strict_types=1);

namespace Mush\Triumph\ConfigData;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\Triumph\Dto\TriumphConfigDto;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphScope;

abstract class TriumphConfigData
{
    /**
     * @return array<TriumphConfigDto>
     */
    public static function getAll(): array
    {
        return [
            new TriumphConfigDto(
                key: TriumphEnum::CYCLE_HUMAN->toConfigKey('default'),
                name: TriumphEnum::CYCLE_HUMAN,
                targetedEvent: PlayerCycleEvent::PLAYER_NEW_CYCLE,
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
            new TriumphConfigDto(
                key: TriumphEnum::CHUN_LIVES->toConfigKey('default'),
                name: TriumphEnum::CHUN_LIVES,
                targetedEvent: PlayerCycleEvent::PLAYER_NEW_CYCLE,
                targetedEventExpectedTags: [
                    EventEnum::NEW_DAY,
                ],
                scope: TriumphScope::PERSONAL,
                target: CharacterEnum::CHUN,
                quantity: 1,
            ),
            new TriumphConfigDto(
                key: TriumphEnum::RETURN_TO_SOL->toConfigKey('default'),
                name: TriumphEnum::RETURN_TO_SOL,
                targetedEvent: DaedalusEvent::FINISH_DAEDALUS,
                targetedEventExpectedTags: [
                    ActionEnum::RETURN_TO_SOL->toString(),
                ],
                scope: TriumphScope::ALL_HUMAN,
                quantity: 20,
            ),
        ];
    }

    public static function getByName(TriumphEnum $name): TriumphConfigDto
    {
        return current(
            array_filter(self::getAll(), static fn (TriumphConfigDto $dto) => $dto->name === $name)
        );
    }
}
