<?php

namespace Mush\Tests\unit\Equipment\WeaponEffectHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\Config\WeaponEffect\ModifyMaxDamageWeaponEffectConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Equipment\WeaponEffect\ModifyMaxDamageWeaponEffectHandler;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ModifyMaxDamageWeaponEffectHandlerTest extends TestCase
{
    private Daedalus $daedalus;
    private Player $attacker;
    private Player $target;
    private GameItem $blaster;

    private ModifyMaxDamageWeaponEffectHandler $handler;

    protected function setUp(): void
    {
        $this->setupDaedalusAndPlayers();

        $this->handler = new ModifyMaxDamageWeaponEffectHandler();
    }

    public function testShouldModifyMaxDamageSpread(): void
    {
        $damageSpread = new DamageSpread(0, 0);
        $effect = $this->givenModifyMaxDamageWeaponEffectWithDamageSpread($damageSpread, modification: 1);

        // when I handle the effect
        $this->handler->handle($effect);

        // then damage spread should be modified
        self::assertEquals(1, $effect->getDamageSpread()->max);
    }

    private function givenModifyMaxDamageWeaponEffectWithDamageSpread(DamageSpread $damageSpread, int $modification): WeaponEffect
    {
        return new WeaponEffect(
            weaponEffectConfig: new ModifyMaxDamageWeaponEffectConfig(
                name: 'test',
                eventName: 'test',
                quantity: $modification,
            ),
            attacker: $this->attacker,
            target: $this->target,
            weapon: $this->blaster,
            damageSpread: $damageSpread,
        );
    }

    private function setupDaedalusAndPlayers(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->attacker = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $this->target = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $this->blaster = GameEquipmentFactory::createItemByNameForHolder(
            name: ItemEnum::BLASTER,
            holder: $this->attacker,
        );
    }
}
