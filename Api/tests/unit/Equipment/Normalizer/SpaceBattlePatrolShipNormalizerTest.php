<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Equipment\Normalizer;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Normalizer\SpaceBattlePatrolShipNormalizer;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SpaceBattlePatrolShipNormalizerTest extends TestCase
{
    private SpaceBattlePatrolShipNormalizer $normalizer;

    /** @var Mockery\Mock|TranslationServiceInterface */
    private TranslationServiceInterface $translationService;

    protected function setUp(): void
    {
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);
        $this->normalizer = new SpaceBattlePatrolShipNormalizer($this->translationService);
    }

    public function testSupportsNormalizationReturnsTrueForPatrolShip(): void
    {
        $patrolShip = $this->createMock(SpaceShip::class);
        $patrolShip->method('getName')->willReturn(EquipmentEnum::PATROL_SHIP);

        self::assertTrue($this->normalizer->supportsNormalization($patrolShip));
    }

    public function testSupportsNormalizationReturnsFalseForNonPatrolShip(): void
    {
        $door = $this->createMock(SpaceShip::class);
        $door->method('getPatrolShipName')->willReturn(EquipmentEnum::DOOR);

        self::assertFalse($this->normalizer->supportsNormalization($door));
    }

    public function testNormalizeReturnsExpectedArray(): void
    {
        $daedalus = $this->createMock(Daedalus::class);
        $patrolShip = $this->createMock(SpaceShip::class);
        $patrolShipArmor = $this->createMock(ChargeStatus::class);
        $patrolShipCharges = $this->createMock(ChargeStatus::class);
        $patrolShipPilot = $this->createMock(Player::class);
        $place = $this->createMock(Place::class);
        $placePlayers = $this->createMock(PlayerCollection::class);

        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $patrolShip->method('getId')->willReturn(1);
        $patrolShip->method('getPatrolShipName')->willReturn(EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS);
        $patrolShip->method('getChargeStatusByName')->willReturnMap([
            [EquipmentStatusEnum::PATROL_SHIP_ARMOR, $patrolShipArmor],
            [EquipmentStatusEnum::ELECTRIC_CHARGES, $patrolShipCharges],
        ]);
        $patrolShip->method('getPlace')->willReturn($place);
        $patrolShip->method('getDaedalus')->willReturn($daedalus);

        $patrolShipArmor->method('getCharge')->willReturn(10);

        $patrolShipCharges->method('getCharge')->willReturn(10);

        $patrolShipPilot->method('getName')->willReturn(CharacterEnum::CHUN);

        $placePlayers->method('first')->willReturn($patrolShipPilot);

        $place->method('getAlivePlayers')->willReturn($placePlayers);

        $this->translationService
            ->shouldReceive('translate')
            ->with(
                EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS,
                [],
                'equipment',
                LanguageEnum::FRENCH
            )
            ->andReturn('Patrouilleur Wallis')
            ->once();

        $expected = [
            'id' => 1,
            'key' => EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS,
            'name' => 'Patrouilleur Wallis',
            'armor' => 10,
            'charges' => 10,
            'pilot' => CharacterEnum::CHUN,
            'drone' => false,
            'isBroken' => false,
        ];

        self::assertSame($expected, $this->normalizer->normalize($patrolShip));
    }

    public function testNormalizeReturnsExpectedArrayForPasiphae(): void
    {
        $daedalus = $this->createMock(Daedalus::class);
        $patrolShip = $this->createMock(SpaceShip::class);
        $patrolShipArmor = $this->createMock(ChargeStatus::class);
        $patrolShipCharges = $this->createMock(ChargeStatus::class);
        $patrolShipPilot = $this->createMock(Player::class);
        $place = $this->createMock(Place::class);
        $placePlayers = $this->createMock(PlayerCollection::class);

        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $patrolShip->method('getId')->willReturn(1);
        $patrolShip->method('getPatrolShipName')->willReturn(EquipmentEnum::PASIPHAE);
        $patrolShip->method('getChargeStatusByName')->willReturnMap([
            [EquipmentStatusEnum::PATROL_SHIP_ARMOR, $patrolShipArmor],
        ]);
        $patrolShip->method('getPlace')->willReturn($place);
        $patrolShip->method('getDaedalus')->willReturn($daedalus);

        $patrolShipArmor->method('getCharge')->willReturn(10);

        $patrolShipCharges->method('getCharge')->willReturn(10);

        $patrolShipPilot->method('getName')->willReturn(CharacterEnum::CHUN);

        $place->method('getAlivePlayers')->willReturn($placePlayers);

        $placePlayers->method('first')->willReturn($patrolShipPilot);

        $this->translationService
            ->shouldReceive('translate')
            ->with(
                EquipmentEnum::PASIPHAE,
                [],
                'equipment',
                LanguageEnum::FRENCH
            )
            ->andReturn('Pasiphae')
            ->once();

        $expected = [
            'id' => 1,
            'key' => EquipmentEnum::PASIPHAE,
            'name' => 'Pasiphae',
            'armor' => 10,
            'charges' => null,
            'pilot' => CharacterEnum::CHUN,
            'drone' => false,
            'isBroken' => false,
        ];

        self::assertSame($expected, $this->normalizer->normalize($patrolShip));
    }

    public function testNormalizeReturnsExpectedArrayWithDeadPilot(): void
    {
        $daedalus = $this->createMock(Daedalus::class);
        $patrolShip = $this->createMock(SpaceShip::class);
        $patrolShipArmor = $this->createMock(ChargeStatus::class);
        $patrolShipCharges = $this->createMock(ChargeStatus::class);
        $place = $this->createMock(Place::class);
        $placePlayers = $this->createMock(PlayerCollection::class);

        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $patrolShip->method('getId')->willReturn(1);
        $patrolShip->method('getPatrolShipName')->willReturn(EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS);
        $patrolShip->method('getChargeStatusByName')->willReturnMap([
            [EquipmentStatusEnum::PATROL_SHIP_ARMOR, $patrolShipArmor],
            [EquipmentStatusEnum::ELECTRIC_CHARGES, $patrolShipCharges],
        ]);
        $patrolShip->method('getPlace')->willReturn($place);
        $patrolShip->method('getDaedalus')->willReturn($daedalus);

        $patrolShipArmor->method('getCharge')->willReturn(10);

        $patrolShipCharges->method('getCharge')->willReturn(10);

        $place->method('getPlayers')->willReturn($placePlayers);

        $placePlayers->method('first')->willReturn(new PlayerCollection());

        $this->translationService
            ->shouldReceive('translate')
            ->with(
                EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS,
                [],
                'equipment',
                LanguageEnum::FRENCH
            )
            ->andReturn('Patrouilleur Wallis')
            ->once();

        $expected = [
            'id' => 1,
            'key' => EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS,
            'name' => 'Patrouilleur Wallis',
            'armor' => 10,
            'charges' => 10,
            'pilot' => null,
            'drone' => false,
            'isBroken' => false,
        ];

        self::assertSame($expected, $this->normalizer->normalize($patrolShip));
    }

    public function testNormalizeWithBrokenPatrolShip(): void
    {
        $daedalus = $this->createMock(Daedalus::class);
        $patrolShip = $this->createMock(SpaceShip::class);
        $patrolShipArmor = $this->createMock(ChargeStatus::class);
        $patrolShipCharges = $this->createMock(ChargeStatus::class);
        $patrolShipPilot = $this->createMock(Player::class);
        $place = $this->createMock(Place::class);
        $placePlayers = $this->createMock(PlayerCollection::class);

        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $patrolShip->method('getId')->willReturn(1);
        $patrolShip->method('getPatrolShipName')->willReturn(EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS);
        $patrolShip->method('getChargeStatusByName')->willReturnMap([
            [EquipmentStatusEnum::PATROL_SHIP_ARMOR, $patrolShipArmor],
            [EquipmentStatusEnum::ELECTRIC_CHARGES, $patrolShipCharges],
        ]);
        $patrolShip->method('getPlace')->willReturn($place);
        $patrolShip->method('getDaedalus')->willReturn($daedalus);
        $patrolShip->method('hasStatus')->with(EquipmentStatusEnum::BROKEN)->willReturn(true);

        $patrolShipArmor->method('getCharge')->willReturn(10);

        $patrolShipCharges->method('getCharge')->willReturn(10);

        $patrolShipPilot->method('getName')->willReturn(CharacterEnum::CHUN);

        $place->method('getAlivePlayers')->willReturn($placePlayers);

        $placePlayers->method('first')->willReturn($patrolShipPilot);

        $this->translationService
            ->shouldReceive('translate')
            ->with(
                EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS,
                [],
                'equipment',
                LanguageEnum::FRENCH
            )
            ->andReturn('Patrouilleur Wallis')
            ->once();

        $expected = [
            'id' => 1,
            'key' => EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS,
            'name' => 'Patrouilleur Wallis',
            'armor' => 10,
            'charges' => 10,
            'pilot' => CharacterEnum::CHUN,
            'drone' => false,
            'isBroken' => true,
        ];

        self::assertSame($expected, $this->normalizer->normalize($patrolShip));
    }

    public function testNormalizePatrolShipWithPilotDrone(): void
    {
        $patrolShip = $this->givenAPatrolShipInBattle();

        $this->givenAPilotDroneInThePatrolShip($patrolShip);

        // given universe is setup correctly
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
                [],
                'equipment',
                LanguageEnum::FRENCH
            )
            ->andReturn('Patrouilleur Jujube')
            ->once();

        // when I normalize the patrol ship
        $normalizedPatrolShip = $this->normalizer->normalize($patrolShip);

        // then I should get the expected result
        self::assertSame(expected: [
            'id' => $patrolShip->getId(),
            'key' => EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            'name' => 'Patrouilleur Jujube',
            'armor' => 10,
            'charges' => 1,
            'pilot' => null,
            'drone' => true,
            'isBroken' => false,
        ], actual: $normalizedPatrolShip);
    }

    public function testNormalizePatrolShipWithNonPilotDrone(): void
    {
        $patrolShip = $this->givenAPatrolShipInBattle();

        $this->givenANonPilotDroneInThePatrolShip($patrolShip);

        // given universe is setup correctly
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
                [],
                'equipment',
                LanguageEnum::FRENCH
            )
            ->andReturn('Patrouilleur Jujube')
            ->once();

        // when I normalize the patrol ship
        $normalizedPatrolShip = $this->normalizer->normalize($patrolShip);

        // then I should get the expected result
        self::assertSame(expected: [
            'id' => $patrolShip->getId(),
            'key' => EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            'name' => 'Patrouilleur Jujube',
            'armor' => 10,
            'charges' => 1,
            'pilot' => null,
            'drone' => false,
            'isBroken' => false,
        ], actual: $normalizedPatrolShip);
    }

    public function testNormalizePatrolShipWithPilotDroneAndHumanPilot(): void
    {
        $patrolShip = $this->givenAPatrolShipInBattle();

        $this->givenAPilotDroneInThePatrolShip($patrolShip);

        $this->givenChunIsInsideThePatrolShip($patrolShip);

        // given universe is setup correctly
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
                [],
                'equipment',
                LanguageEnum::FRENCH
            )
            ->andReturn('Patrouilleur Jujube')
            ->once();

        // when I normalize the patrol ship
        $normalizedPatrolShip = $this->normalizer->normalize($patrolShip);

        // then I should get the expected result
        self::assertSame(expected: [
            'id' => $patrolShip->getId(),
            'key' => EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            'name' => 'Patrouilleur Jujube',
            'armor' => 10,
            'charges' => 1,
            'pilot' => CharacterEnum::CHUN,
            'drone' => true,
            'isBroken' => false,
        ], actual: $normalizedPatrolShip);
    }

    private function givenAPatrolShipInBattle(): GameEquipment
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $patrolShipPlace = Place::createRoomByNameInDaedalus(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN, $daedalus);
        $patrolShip = GameEquipmentFactory::createPatrolShipByNameForHolder(EquipmentEnum::PATROL_SHIP, $patrolShipPlace);
        StatusFactory::createChargeStatusFromStatusName(EquipmentStatusEnum::PATROL_SHIP_ARMOR, $patrolShip);
        StatusFactory::createChargeStatusFromStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES, $patrolShip);

        return $patrolShip;
    }

    private function givenAPilotDroneInThePatrolShip(GameEquipment $patrolShip): GameEquipment
    {
        $drone = GameEquipmentFactory::createDroneForHolder($patrolShip->getPlace());
        StatusFactory::createStatusByNameForHolder(EquipmentStatusEnum::PILOT_DRONE_UPGRADE, $drone);

        return $drone;
    }

    private function givenANonPilotDroneInThePatrolShip(GameEquipment $patrolShip): GameEquipment
    {
        return GameEquipmentFactory::createDroneForHolder($patrolShip->getPlace());
    }

    private function givenChunIsInsideThePatrolShip(GameEquipment $patrolShip): void
    {
        $chun = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $patrolShip->getDaedalus());
        $chun->changePlace($patrolShip->getPlace());
    }
}
