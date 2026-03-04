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
use Mush\Equipment\WeaponEffect\ModifyMaxDamageFromSkilledAttackerWeaponEffectHandler;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ModifyMaxDamageFromSkilledAttackerWeaponEffectHandlerTest extends TestCase
{
    private Daedalus $daedalus;
    private Player $attacker;
    private Player $target;
    private GameItem $blaster;

    private ModifyMaxDamageFromSkilledAttackerWeaponEffectHandler $handler;

    protected function setUp(): void
    {
        $this->setupDaedalusAndPlayers();

        $this->handler = new ModifyMaxDamageFromSkilledAttackerWeaponEffectHandler();
    }

    public function testNotShouldModifyMaxDamageSpreadByDefault(): void
    {
        $damageSpread = new DamageSpread(0, 0);
        $effect = $this->givenModifyMaxDamageWeaponEffectWithDamageSpread($damageSpread);

        // when I handle the effect
        $this->handler->handle($effect);

        // then damage spread should not be modified
        self::assertEquals(0, $effect->getDamageSpread()->max);
    }

    public function testShouldModifyMaxDamageSpreadForSolid(): void
    {
        $damageSpread = new DamageSpread(0, 0);
        $effect = $this->givenModifyMaxDamageWeaponEffectWithDamageSpread($damageSpread);

        // given the attacker is solid
        Skill::createByNameForPlayer(SkillEnum::SOLID, $this->attacker);

        // when I handle the effect
        $this->handler->handle($effect);

        // then damage spread should be modified by 1
        self::assertEquals(1, $effect->getDamageSpread()->max);
    }

    public function testShouldModifyMaxDamageSpreadForWrestler(): void
    {
        $damageSpread = new DamageSpread(0, 0);
        $effect = $this->givenModifyMaxDamageWeaponEffectWithDamageSpread($damageSpread);

        // given the attacker is wrestler
        Skill::createByNameForPlayer(SkillEnum::WRESTLER, $this->attacker);

        // when I handle the effect
        $this->handler->handle($effect);

        // then damage spread should be modified by 2
        self::assertEquals(2, $effect->getDamageSpread()->max);
    }

    private function givenModifyMaxDamageWeaponEffectWithDamageSpread(DamageSpread $damageSpread): WeaponEffect
    {
        return new WeaponEffect(
            weaponEffectConfig: new ModifyMaxDamageWeaponEffectConfig(
                name: 'test',
                eventName: 'test',
                quantity: 0,
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
