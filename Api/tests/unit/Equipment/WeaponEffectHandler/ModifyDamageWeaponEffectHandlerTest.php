<?php

namespace Mush\Tests\unit\Equipment\WeaponEffectHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\Config\WeaponEffect\ModifyDamageWeaponEffectConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Equipment\WeaponEffect\ModifyDamageWeaponEffectHandler;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ModifyDamageWeaponEffectHandlerTest extends TestCase
{
    private Daedalus $daedalus;
    private Player $attacker;
    private Player $target;
    private GameItem $blaster;

    private ModifyDamageWeaponEffectHandler $handler;

    protected function setUp(): void
    {
        $this->setupDaedalusAndPlayers();

        $this->handler = new ModifyDamageWeaponEffectHandler();
    }

    public function testShouldModifyDamageSpread(): void
    {
        // given
        $damageSpread = new DamageSpread(0, 0);
        $effect = $this->givenModifyDamageWeaponEffectWithDamageSpread($damageSpread, modification: 1);

        // when I handle the effect
        $this->handler->handle($effect);

        // then damage spread should be modified
        self::assertEquals(1, $effect->getDamageSpread()->min);
        self::assertEquals(1, $effect->getDamageSpread()->max);
    }

    private function givenModifyDamageWeaponEffectWithDamageSpread(DamageSpread $damageSpread, int $modification): WeaponEffect
    {
        return new WeaponEffect(
            weaponEffectConfig: new ModifyDamageWeaponEffectConfig(
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
