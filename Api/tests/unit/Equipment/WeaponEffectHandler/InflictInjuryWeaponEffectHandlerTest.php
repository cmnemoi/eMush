<?php

declare(strict_types=1);

namespace Mush\tests\unit\Equipment\WeaponEffectHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Repository\InMemoryPlayerDiseaseRepository;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Equipment\Entity\Config\WeaponEffect\InflictInjuryWeaponEffectConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Equipment\WeaponEffect\InflictInjuryWeaponEffectHandler;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class InflictInjuryWeaponEffectHandlerTest extends TestCase
{
    private FakeD100RollService $d100Roll;
    private InflictInjuryWeaponEffectHandler $handler;

    private Daedalus $daedalus;
    private Player $attacker;
    private Player $target;
    private GameItem $blaster;

    protected function setUp(): void
    {
        $this->d100Roll = new FakeD100RollService();
        $playerDiseaseService = new PlayerDiseaseService(
            d100Roll: new FakeD100RollService(),
            eventService: self::createStub(EventServiceInterface::class),
            randomService: self::createStub(RandomServiceInterface::class),
            playerDiseaseRepository: new InMemoryPlayerDiseaseRepository(),
        );

        $this->handler = new InflictInjuryWeaponEffectHandler(
            d100Roll: $this->d100Roll,
            playerDiseaseService: $playerDiseaseService,
        );

        $this->setupDaedalusAndPlayers();
    }

    public function testShouldInflictInjuryWeaponToTarget(): void
    {
        // given inflict injury weapon effect to target
        $effect = $this->createInflictInjuryToTargetWeaponEffect();

        // when I handle the inflict injury weapon effect
        $this->handler->handle($effect);

        // then target should be injured
        self::assertTrue(
            $effect->getTarget()->getMedicalConditionByNameOrThrow(InjuryEnum::DAMAGED_EARS)->isActive(),
            'Target should be injured'
        );
    }

    public function testShouldInflictInjuryWeaponToShooter(): void
    {
        // given inflict injury weapon effect to shooter
        $effect = $this->createInflictInjuryToShooterWeaponEffect();

        // when I handle the inflict injury weapon effect
        $this->handler->handle($effect);

        // then shooter should be injured
        self::assertTrue(
            $effect->getAttacker()->getMedicalConditionByNameOrThrow(InjuryEnum::DAMAGED_EARS)->isActive(),
            'Shooter should be injured'
        );
    }

    public function testShouldNotInflictInjuryIfRollFails(): void
    {
        // given inflict injury weapon effect
        $effect = $this->createInflictInjuryToTargetWeaponEffect();

        // given roll will fail
        $this->d100Roll->makeFail();

        // when I handle the inflict injury weapon effect
        $this->handler->handle($effect);

        // then target should not be injured
        self::assertNull(
            $effect->getTarget()->getMedicalConditionByName(InjuryEnum::DAMAGED_EARS),
            'Target should not be injured'
        );
    }

    private function createInflictInjuryToTargetWeaponEffect(): WeaponEffect
    {
        return new WeaponEffect(
            weaponEffectConfig: EventConfigData::getWeaponEffectConfigDataByName(WeaponEffectEnum::INFLICT_MASHED_EAR_INJURY_TO_TARGET)->toEntity(),
            attacker: $this->attacker,
            target: $this->target,
            weapon: $this->blaster,
            damageSpread: new DamageSpread(0, 0),
        );
    }

    private function createInflictInjuryToShooterWeaponEffect(): WeaponEffect
    {
        return new WeaponEffect(
            weaponEffectConfig: new InflictInjuryWeaponEffectConfig(
                name: 'test',
                eventName: 'test',
                injuryName: InjuryEnum::DAMAGED_EARS,
                triggerRate: 100,
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
