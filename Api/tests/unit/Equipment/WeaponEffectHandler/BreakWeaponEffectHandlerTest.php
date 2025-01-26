<?php

declare(strict_types=1);

namespace Mush\tests\unit\Equipment\WeaponEffectHandler;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Equipment\WeaponEffect\BreakWeaponEffectHandler;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Service\FakeStatusService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class BreakWeaponEffectHandlerTest extends TestCase
{
    private BreakWeaponEffectHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new BreakWeaponEffectHandler(
            statusService: new FakeStatusService(),
        );
    }

    public function testShouldBreakWeapon(): void
    {
        // given break weapon effect
        $effect = $this->createBreakWeaponEffect();

        // when I handle the break weapon effect
        $this->handler->handle($effect);

        // then the weapon should be broken
        self::assertTrue($effect->getWeapon()->isBroken());
    }

    private function createBreakWeaponEffect(): WeaponEffect
    {
        return new WeaponEffect(
            weaponEffectConfig: EventConfigData::getWeaponEffectConfigDataByName(WeaponEffectEnum::BREAK_WEAPON)->toEntity(),
            attacker: PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus()),
            target: PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus()),
            weapon: GameEquipmentFactory::createItemByNameForHolder(
                name: ItemEnum::BLASTER,
                holder: PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus()),
            ),
            damageSpread: new DamageSpread(0, 0),
        );
    }
}
