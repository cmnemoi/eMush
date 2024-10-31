<?php

declare(strict_types=1);

namespace Mush\tests\unit\Equipment\WeaponEffectHandler;

use Mockery\Spy;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Equipment\WeaponEffect\DropWeaponEffectHandler;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Player\Factory\PlayerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DropWeaponEffectHandlerTest extends TestCase
{
    private DropWeaponEffectHandler $handler;

    /** @var GameEquipmentServiceInterface|Spy */
    private GameEquipmentServiceInterface $gameEquipmentService;

    protected function setUp(): void
    {
        $this->gameEquipmentService = \Mockery::spy(GameEquipmentServiceInterface::class);

        $this->handler = new DropWeaponEffectHandler(
            gameEquipmentService: $this->gameEquipmentService,
        );
    }

    public function testShouldDropWeapon(): void
    {
        // given drop weapon effect
        $effect = $this->createDropWeaponEffect();

        // when I handle the drop weapon effect
        $this->handler->handle($effect);

        // then the weapon should be in attacker's room
        $this->gameEquipmentService->shouldHaveReceived('moveEquipmentTo')->once();
    }

    private function createDropWeaponEffect(): WeaponEffect
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $shooter = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $target = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $weapon = GameEquipmentFactory::createItemByNameForHolder(
            name: ItemEnum::BLASTER,
            holder: $shooter,
        );

        return new WeaponEffect(
            weaponEffectConfig: EventConfigData::getWeaponEffectConfigDataByName(WeaponEffectEnum::DROP_WEAPON)->toEntity(),
            attacker: $shooter,
            target: $target,
            weapon: $weapon,
            damageSpread: new DamageSpread(0, 0),
        );
    }
}
