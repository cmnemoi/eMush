<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Equipment\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Normalizer\SpaceBattleTurretNormalizer;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;

final class SpaceBattleTurretNormalizerTest extends TestCase
{
    private SpaceBattleTurretNormalizer $normalizer;
    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    protected function setUp(): void
    {
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);

        $this->normalizer = new SpaceBattleTurretNormalizer($this->translationService);
    }

    public function testSupportsNormalizationReturnsTrueForTurretCommand(): void
    {
        $turret = $this->createMock(GameEquipment::class);
        $turret->method('getName')->willReturn(EquipmentEnum::TURRET_COMMAND);

        $this->assertTrue($this->normalizer->supportsNormalization($turret));
    }

    public function testSupportsNormalizationReturnsFalseForNonTurretCommand(): void
    {
        $turret = $this->createMock(GameEquipment::class);
        $turret->method('getName')->willReturn(EquipmentEnum::DOOR);

        $this->assertFalse($this->normalizer->supportsNormalization($turret));
    }

    public function testNormalizeReturnsExpectedArray(): void
    {
        $chargeStatus = $this->createMock(ChargeStatus::class);
        $daedalus = $this->createMock(Daedalus::class);
        $place = $this->createMock(Place::class);
        $player1 = $this->createMock(Player::class);
        $player2 = $this->createMock(Player::class);
        $turret = $this->createMock(GameEquipment::class);
        $turretOccupiers = $this->createMock(PlayerCollection::class);

        $chargeStatus->method('getCharge')->willReturn(4);

        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $place->method('getName')->willReturn(RoomEnum::REAR_ALPHA_TURRET);
        $place->method('getPlayers')->willReturn(new PlayerCollection([$player1, $player2]));

        $player1->method('getName')->willReturn(CharacterEnum::CHUN);
        $player1->method('getPlace')->willReturn($place);
        $player1->method('isAlive')->willReturn(true);
        $player2->method('getName')->willReturn(CharacterEnum::ANDIE);
        $player2->method('getPlace')->willReturn($place);
        $player2->method('isAlive')->willReturn(true);

        $turret->method('getId')->willReturn(1);
        $turret->method('getPlace')->willReturn($place);
        $turret->method('getStatusByName')->willReturn($chargeStatus);
        $turret->method('getDaedalus')->willReturn($daedalus);

        $turretOccupiers->method('isEmpty')->willReturn(false);
        $turretOccupiers->method('getPlayerAlive')->willReturn($turretOccupiers);

        $this->translationService
            ->shouldReceive('translate')
            ->with(RoomEnum::REAR_ALPHA_TURRET, [], 'room', LanguageEnum::FRENCH)
            ->andReturn('Tourelle Alpha Arrière')
            ->once()
        ;

        $expected = [
            'id' => 1,
            'key' => RoomEnum::REAR_ALPHA_TURRET,
            'name' => 'Tourelle Alpha Arrière',
            'charges' => 4,
            'occupiers' => [CharacterEnum::CHUN, CharacterEnum::ANDIE],
            'isBroken' => false,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($turret));
    }

    public function testNormalizeReturnsNullOccupiersForEmptyCollection(): void
    {
        $chargeStatus = $this->createMock(ChargeStatus::class);
        $daedalus = $this->createMock(Daedalus::class);
        $place = $this->createMock(Place::class);
        $turret = $this->createMock(GameEquipment::class);
        $turretOccupiers = $this->createMock(PlayerCollection::class);

        $chargeStatus->method('getCharge')->willReturn(4);
        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $place->method('getName')->willReturn(RoomEnum::REAR_ALPHA_TURRET);
        $place->method('getPlayers')->willReturn(new PlayerCollection());

        $turret->method('getId')->willReturn(1);
        $turret->method('getPlace')->willReturn($place);
        $turret->method('getStatusByName')->willReturn($chargeStatus);
        $turret->method('getDaedalus')->willReturn($daedalus);

        $turretOccupiers->method('getPlayerAlive')->willReturn($turretOccupiers);

        $this->translationService
            ->shouldReceive('translate')
            ->with(RoomEnum::REAR_ALPHA_TURRET, [], 'room', LanguageEnum::FRENCH)
            ->andReturn('Tourelle Alpha Arrière')
            ->once()
        ;

        $expected = [
            'id' => 1,
            'key' => RoomEnum::REAR_ALPHA_TURRET,
            'name' => 'Tourelle Alpha Arrière',
            'charges' => 4,
            'occupiers' => [],
            'isBroken' => false,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($turret));
    }

    public function testNormalizeWithBrokenTurret(): void
    {
        $chargeStatus = $this->createMock(ChargeStatus::class);
        $daedalus = $this->createMock(Daedalus::class);
        $place = $this->createMock(Place::class);
        $player1 = $this->createMock(Player::class);
        $player2 = $this->createMock(Player::class);
        $turret = $this->createMock(GameEquipment::class);
        $turretOccupiers = $this->createMock(PlayerCollection::class);

        $chargeStatus->method('getCharge')->willReturn(4);

        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $place->method('getName')->willReturn(RoomEnum::REAR_ALPHA_TURRET);
        $place->method('getPlayers')->willReturn(new PlayerCollection([$player1, $player2]));

        $player1->method('getName')->willReturn(CharacterEnum::CHUN);
        $player1->method('getPlace')->willReturn($place);
        $player1->method('isAlive')->willReturn(true);
        $player2->method('getName')->willReturn(CharacterEnum::ANDIE);
        $player2->method('getPlace')->willReturn($place);
        $player2->method('isAlive')->willReturn(true);

        $turret->method('getId')->willReturn(1);
        $turret->method('getPlace')->willReturn($place);
        $turret->method('getStatusByName')->willReturn($chargeStatus);
        $turret->method('getDaedalus')->willReturn($daedalus);
        $turret->method('hasStatus')->with(EquipmentStatusEnum::BROKEN)->willReturn(true);

        $turretOccupiers->method('isEmpty')->willReturn(false);
        $turretOccupiers->method('getPlayerAlive')->willReturn($turretOccupiers);

        $this->translationService
            ->shouldReceive('translate')
            ->with(RoomEnum::REAR_ALPHA_TURRET, [], 'room', LanguageEnum::FRENCH)
            ->andReturn('Tourelle Alpha Arrière')
            ->once()
        ;

        $expected = [
            'id' => 1,
            'key' => RoomEnum::REAR_ALPHA_TURRET,
            'name' => 'Tourelle Alpha Arrière',
            'charges' => 4,
            'occupiers' => [CharacterEnum::CHUN, CharacterEnum::ANDIE],
            'isBroken' => true,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($turret));
    }
}
