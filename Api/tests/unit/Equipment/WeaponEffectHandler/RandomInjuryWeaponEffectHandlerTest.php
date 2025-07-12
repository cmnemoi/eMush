<?php

declare(strict_types=1);

namespace Mush\tests\unit\Equipment\WeaponEffectHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Repository\InMemoryPlayerDiseaseRepository;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Mush\Disease\Service\DiseaseCauseService;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Event\WeaponEffect;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\ValueObject\DamageSpread;
use Mush\Equipment\WeaponEffect\RandomInjuryWeaponEffectHandler;
use Mush\Game\ConfigData\EventConfigData;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService as FakeD100Roll;
use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use Mush\Game\Service\Random\ProbaCollectionRandomElementService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Factory\PlayerFactory;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RandomInjuryWeaponEffectHandlerTest extends TestCase
{
    private Daedalus $daedalus;
    private RandomInjuryWeaponEffectHandler $handler;

    protected function setUp(): void
    {
        $this->daedalus = DaedalusFactory::createDaedalus();

        /** @var EventServiceInterface|Stub $eventService */
        $eventService = self::createStub(EventServiceInterface::class);

        /** @var RandomServiceInterface|Stub $randomService */
        $randomService = self::createStub(RandomServiceInterface::class);

        /** @var ConsumableDiseaseServiceInterface|Stub $consumableDiseaseService */
        $consumableDiseaseService = self::createStub(ConsumableDiseaseServiceInterface::class);

        $this->handler = new RandomInjuryWeaponEffectHandler(
            new DiseaseCauseService(
                playerDiseaseService: new PlayerDiseaseService(
                    d100Roll: new FakeD100Roll(),
                    eventService: $eventService,
                    randomService: $randomService,
                    playerDiseaseRepository: new InMemoryPlayerDiseaseRepository(),
                ),
                d100Roll: new FakeD100Roll(),
                probaCollectionRandomElement: new ProbaCollectionRandomElementService(
                    new FakeGetRandomIntegerService(result: 0)
                ),
                consumableDiseaseService: $consumableDiseaseService,
            ),
            new FakeD100Roll()
        );
    }

    public function testShouldInjureTarget(): void
    {
        // given random injury weapon effect
        $effect = $this->createRandomInjuryForTargetWeaponEffect();

        // when I handle the random injury effect
        $this->handler->handle($effect);

        // then the target should be injured
        self::assertTrue($effect->getTarget()->getMedicalConditions()->filter(static fn (PlayerDisease $disease) => $disease->isAnInjury())->count() > 0);
    }

    public function testShouldInjureShooter(): void
    {
        // given random injury weapon effect
        $effect = $this->createRandomInjuryForShooterWeaponEffect();

        // when I handle the random injury effect
        $this->handler->handle($effect);

        // then the shooter should be injured
        self::assertTrue($effect->getAttacker()->getMedicalConditions()->filter(static fn (PlayerDisease $disease) => $disease->isAnInjury())->count() > 0);
    }

    private function createRandomInjuryForTargetWeaponEffect(): WeaponEffect
    {
        return new WeaponEffect(
            weaponEffectConfig: EventConfigData::getWeaponEffectConfigDataByName(WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET)->toEntity(),
            attacker: PlayerFactory::createPlayerWithDaedalus($this->daedalus),
            target: PlayerFactory::createPlayerWithDaedalus($this->daedalus),
            weapon: GameEquipmentFactory::createItemByNameForHolder(
                name: ItemEnum::BLASTER,
                holder: PlayerFactory::createPlayerWithDaedalus($this->daedalus),
            ),
            damageSpread: new DamageSpread(0, 0),
        );
    }

    private function createRandomInjuryForShooterWeaponEffect(): WeaponEffect
    {
        return new WeaponEffect(
            weaponEffectConfig: EventConfigData::getWeaponEffectConfigDataByName(WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_SHOOTER)->toEntity(),
            attacker: PlayerFactory::createPlayerWithDaedalus($this->daedalus),
            target: PlayerFactory::createPlayerWithDaedalus($this->daedalus),
            weapon: GameEquipmentFactory::createItemByNameForHolder(
                name: ItemEnum::BLASTER,
                holder: PlayerFactory::createPlayerWithDaedalus($this->daedalus),
            ),
            damageSpread: new DamageSpread(0, 0),
        );
    }
}
