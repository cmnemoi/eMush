<?php

declare(strict_types=1);

namespace Mush\tests\unit\Equipment\WeaponEffectHandler;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Equipment\WeaponEffect\OneShotWeaponEffectHandler;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Repository\ClosedPlayerRepositoryInterface;
use Mush\Player\Repository\InMemoryPlayerRepository;
use Mush\Player\Repository\PlayerInfoRepositoryInterface;
use Mush\Player\Service\PlayerService;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class OneShotWeaponEffectHandlerTest extends TestCase
{
    private OneShotWeaponEffectHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new OneShotWeaponEffectHandler(
            new PlayerService(
                closedPlayerRepository: self::createStub(ClosedPlayerRepositoryInterface::class),
                daedalusRepository: self::createStub(DaedalusRepositoryInterface::class),
                eventService: self::createStub(EventServiceInterface::class),
                playerRepository: new InMemoryPlayerRepository(),
                roomLogService: self::createStub(RoomLogServiceInterface::class),
                playerInfoRepository: self::createStub(PlayerInfoRepositoryInterface::class),
            )
        );
    }

    public function testShouldKillTarget(): void
    {
        // given one shot weapon effect
        $effect = new WeaponEffect(
            weaponEffectConfig: EventConfigData::getWeaponEffectConfigDataByName(WeaponEffectEnum::BLASTER_ONE_SHOT)->toEntity(),
            attacker: PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus()),
            target: PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus()),
            weapon: GameEquipmentFactory::createItemByNameForHolder(
                name: ItemEnum::BLASTER,
                holder: PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus()),
            ),
            damageSpread: new DamageSpread(0, 0),
        );

        // when I handle the one shot effect
        $this->handler->handle(
            $effect
        );

        // then the target should be dead
        self::assertTrue($effect->getTarget()->isDead());
    }
}
