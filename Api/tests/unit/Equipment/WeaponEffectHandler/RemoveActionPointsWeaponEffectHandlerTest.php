<?php

namespace Mush\Tests\unit\Equipment\WeaponEffectHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\Config\WeaponEffect\RemoveActionPointsWeaponEffectConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Equipment\WeaponEffect\RemoveActionPointsWeaponEffectHandler;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Service\RemoveActionPointsFromPlayerServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RemoveActionPointsWeaponEffectHandlerTest extends TestCase
{
    private Daedalus $daedalus;
    private Player $attacker;
    private Player $target;
    private GameItem $blaster;

    private RemoveActionPointsWeaponEffectHandler $handler;

    protected function setUp(): void
    {
        $this->setupDaedalusAndPlayers();

        $this->handler = new RemoveActionPointsWeaponEffectHandler(
            removeActionPointsFromPlayer: new FakeRemoveActionPointsFromPlayer(),
        );
    }

    public function testShouldRemoveActionPointsToTarget(): void
    {
        // given effect which removes 1 action point to target
        $effect = $this->givenRemoveActionPointsToTargetWeaponEffect(modification: 1);

        // given target has 2 action points
        $this->target->setActionPoint(2);

        // when I handle the effect
        $this->handler->handle($effect);

        // then target should have lost 1 action point
        self::assertEquals(1, $this->target->getActionPoint(), 'Target should have lost 1 action point');
    }

    public function testShouldRemoveActionPointsToAttacker(): void
    {
        // given effect which removes 1 action point to attacker
        $effect = $this->givenRemoveActionPointsToAttackerWeaponEffect(modification: 1);

        // given attacker has 2 action points
        $this->attacker->setActionPoint(2);

        // when I handle the effect
        $this->handler->handle($effect);

        // then attacker should have lost 1 action point
        self::assertEquals(1, $this->attacker->getActionPoint(), 'Attacker should have lost 1 action point');
    }

    private function givenRemoveActionPointsToTargetWeaponEffect(int $modification): WeaponEffect
    {
        return new WeaponEffect(
            weaponEffectConfig: new RemoveActionPointsWeaponEffectConfig(
                name: 'test',
                eventName: 'test',
                quantity: $modification,
                toShooter: false,
            ),
            attacker: $this->attacker,
            target: $this->target,
            weapon: $this->blaster,
            damageSpread: new DamageSpread(0, 0),
        );
    }

    private function givenRemoveActionPointsToAttackerWeaponEffect(int $modification): WeaponEffect
    {
        return new WeaponEffect(
            weaponEffectConfig: new RemoveActionPointsWeaponEffectConfig(
                name: 'test',
                eventName: 'test',
                quantity: $modification,
                toShooter: true,
            ),
            attacker: $this->attacker,
            target: $this->target,
            weapon: $this->blaster,
            damageSpread: new DamageSpread(0, 0),
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

final class FakeRemoveActionPointsFromPlayer implements RemoveActionPointsFromPlayerServiceInterface
{
    public function execute(int $quantity, Player $player, array $tags = [], ?Player $author = null, \DateTime $time = new \DateTime(), string $visibility = VisibilityEnum::HIDDEN): void
    {
        $player->setActionPoint($player->getActionPoint() - $quantity);
    }
}
