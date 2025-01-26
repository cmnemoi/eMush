<?php

declare(strict_types=1);

namespace Mush\tests\unit\Equipment\WeaponEffectHandler;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\Config\WeaponEffect\MultiplyDamageOnMushTargetWeaponEffectConfig;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Equipment\WeaponEffect\MultiplyDamageOnMushTargetWeaponEffectHandler;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MultiplyDamageOnMushTargetWeaponEffectHandlerTest extends TestCase
{
    private MultiplyDamageOnMushTargetWeaponEffectHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new MultiplyDamageOnMushTargetWeaponEffectHandler();
    }

    public function testShouldMulitplyDamageOnMushTarget(): void
    {
        // given multiply damage on mush target effect
        $effect = $this->createMultiplyDamageOnMushTarget(
            damageSpread: new DamageSpread(1, 1),
            quantity: 2,
        );

        // when I handle the break weapon effect
        $this->handler->handle($effect);

        // then damage spread should be modified
        self::assertTrue($effect->getDamageSpread()->equals(new DamageSpread(2, 2)));
    }

    public function testShouldNotMultiplyDamageOnHumanTarget(): void
    {
        // given multiply damage on human target effect
        $effect = $this->createMultiplyDamageOnHumanTarget(
            damageSpread: new DamageSpread(1, 1),
            quantity: 2,
        );

        // when I handle the break weapon effect
        $this->handler->handle($effect);

        // then damage spread should not be modified
        self::assertTrue($effect->getDamageSpread()->equals(new DamageSpread(1, 1)));
    }

    private function createMultiplyDamageOnMushTarget(DamageSpread $damageSpread, int $quantity): WeaponEffect
    {
        $target = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());
        StatusFactory::createStatusByNameForHolder(PlayerStatusEnum::MUSH, $target);

        return new WeaponEffect(
            weaponEffectConfig: new MultiplyDamageOnMushTargetWeaponEffectConfig(
                name: 'test',
                eventName: 'test',
                quantity: $quantity,
            ),
            attacker: PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus()),
            target: $target,
            weapon: GameEquipmentFactory::createItemByNameForHolder(
                name: ItemEnum::BLASTER,
                holder: PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus()),
            ),
            damageSpread: $damageSpread,
        );
    }

    private function createMultiplyDamageOnHumanTarget(DamageSpread $damageSpread, int $quantity): WeaponEffect
    {
        return new WeaponEffect(
            weaponEffectConfig: new MultiplyDamageOnMushTargetWeaponEffectConfig(
                name: 'test',
                eventName: 'test',
                quantity: $quantity,
            ),
            attacker: PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus()),
            target: PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus()),
            weapon: GameEquipmentFactory::createItemByNameForHolder(
                name: ItemEnum::BLASTER,
                holder: PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus()),
            ),
            damageSpread: $damageSpread,
        );
    }
}
