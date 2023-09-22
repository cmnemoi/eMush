<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Equipment\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Normalizer\SpaceBattlePatrolShipNormalizer;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;

class SpaceBattlePatrolShipNormalizerTest extends TestCase
{
    private SpaceBattlePatrolShipNormalizer $normalizer;
    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    protected function setUp(): void
    {
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);
        $this->normalizer = new SpaceBattlePatrolShipNormalizer($this->translationService);
    }

    public function testSupportsNormalizationReturnsTrueForPatrolShip(): void
    {
        $patrolShip = $this->createMock(GameEquipment::class);
        $patrolShip->method('getName')->willReturn(EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS);

        $this->assertTrue($this->normalizer->supportsNormalization($patrolShip));
    }

    public function testSupportsNormalizationReturnsFalseForNonPatrolShip(): void
    {
        $door = $this->createMock(GameEquipment::class);
        $door->method('getName')->willReturn(EquipmentEnum::DOOR);

        $this->assertFalse($this->normalizer->supportsNormalization($door));
    }

    public function testNormalizeReturnsExpectedArray(): void
    {
        $daedalus = $this->createMock(Daedalus::class);
        $patrolShip = $this->createMock(GameEquipment::class);
        $patrolShipArmor = $this->createMock(ChargeStatus::class);
        $patrolShipCharges = $this->createMock(ChargeStatus::class);
        $patrolShipPilot = $this->createMock(Player::class);
        $place = $this->createMock(Place::class);
        $placePlayers = $this->createMock(PlayerCollection::class);

        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $patrolShip->method('getId')->willReturn(1);
        $patrolShip->method('getName')->willReturn(EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS);
        $patrolShip->method('getStatusByName')->will($this->returnValueMap([
            [EquipmentStatusEnum::PATROL_SHIP_ARMOR, $patrolShipArmor],
            [EquipmentStatusEnum::ELECTRIC_CHARGES, $patrolShipCharges],
        ]));
        $patrolShip->method('getPlace')->willReturn($place);
        $patrolShip->method('getDaedalus')->willReturn($daedalus);

        $patrolShipArmor->method('getCharge')->willReturn(10);

        $patrolShipCharges->method('getCharge')->willReturn(10);

        $patrolShipPilot->method('getName')->willReturn(CharacterEnum::CHUN);

        $place->method('getPlayers')->willReturn($placePlayers);

        $placePlayers->method('getPlayerAlive')->willReturn(new PlayerCollection([$patrolShipPilot]));

        $this->translationService
            ->shouldReceive('translate')
            ->with(
                EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS,
                [],
                'equipment',
                LanguageEnum::FRENCH
            )
            ->andReturn('Patrouilleur Wallis')
            ->once()
        ;

        $expected = [
            'id' => 1,
            'key' => EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS,
            'name' => 'Patrouilleur Wallis',
            'armor' => 10,
            'charges' => 10,
            'pilot' => CharacterEnum::CHUN,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($patrolShip));
    }

    public function testNormalizeReturnsExpectedArrayForPasiphae(): void
    {
        $daedalus = $this->createMock(Daedalus::class);
        $patrolShip = $this->createMock(GameEquipment::class);
        $patrolShipArmor = $this->createMock(ChargeStatus::class);
        $patrolShipCharges = $this->createMock(ChargeStatus::class);
        $patrolShipPilot = $this->createMock(Player::class);
        $place = $this->createMock(Place::class);
        $placePlayers = $this->createMock(PlayerCollection::class);

        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $patrolShip->method('getId')->willReturn(1);
        $patrolShip->method('getName')->willReturn(EquipmentEnum::PASIPHAE);
        $patrolShip->method('getStatusByName')->will($this->returnValueMap([
            [EquipmentStatusEnum::PATROL_SHIP_ARMOR, $patrolShipArmor],
        ]));
        $patrolShip->method('getPlace')->willReturn($place);
        $patrolShip->method('getDaedalus')->willReturn($daedalus);

        $patrolShipArmor->method('getCharge')->willReturn(10);

        $patrolShipCharges->method('getCharge')->willReturn(10);

        $patrolShipPilot->method('getName')->willReturn(CharacterEnum::CHUN);

        $place->method('getPlayers')->willReturn($placePlayers);

        $placePlayers->method('getPlayerAlive')->willReturn(new PlayerCollection([$patrolShipPilot]));

        $this->translationService
            ->shouldReceive('translate')
            ->with(
                EquipmentEnum::PASIPHAE,
                [],
                'equipment',
                LanguageEnum::FRENCH
            )
            ->andReturn('Pasiphae')
            ->once()
        ;

        $expected = [
            'id' => 1,
            'key' => EquipmentEnum::PASIPHAE,
            'name' => 'Pasiphae',
            'armor' => 10,
            'charges' => null,
            'pilot' => CharacterEnum::CHUN,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($patrolShip));
    }

    public function testNormalizeReturnsExpectedArrayWithDeadPilot(): void
    {
        $daedalus = $this->createMock(Daedalus::class);
        $patrolShip = $this->createMock(GameEquipment::class);
        $patrolShipArmor = $this->createMock(ChargeStatus::class);
        $patrolShipCharges = $this->createMock(ChargeStatus::class);
        $place = $this->createMock(Place::class);
        $placePlayers = $this->createMock(PlayerCollection::class);

        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $patrolShip->method('getId')->willReturn(1);
        $patrolShip->method('getName')->willReturn(EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS);
        $patrolShip->method('getStatusByName')->will($this->returnValueMap([
            [EquipmentStatusEnum::PATROL_SHIP_ARMOR, $patrolShipArmor],
            [EquipmentStatusEnum::ELECTRIC_CHARGES, $patrolShipCharges],
        ]));
        $patrolShip->method('getPlace')->willReturn($place);
        $patrolShip->method('getDaedalus')->willReturn($daedalus);

        $patrolShipArmor->method('getCharge')->willReturn(10);

        $patrolShipCharges->method('getCharge')->willReturn(10);

        $place->method('getPlayers')->willReturn($placePlayers);

        $placePlayers->method('getPlayerAlive')->willReturn(new PlayerCollection());

        $this->translationService
            ->shouldReceive('translate')
            ->with(
                EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS,
                [],
                'equipment',
                LanguageEnum::FRENCH
            )
            ->andReturn('Patrouilleur Wallis')
            ->once()
        ;

        $expected = [
            'id' => 1,
            'key' => EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS,
            'name' => 'Patrouilleur Wallis',
            'armor' => 10,
            'charges' => 10,
            'pilot' => null,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($patrolShip));
    }
}
