<?php

declare(strict_types=1);

namespace Tests\Mush\Equipment\Normalizer;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Normalizer\SpaceBattleTurretNormalizer;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use PHPUnit\Framework\TestCase;

final class SpaceBattleTurretNormalizerTest extends TestCase
{
    private SpaceBattleTurretNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new SpaceBattleTurretNormalizer();
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
        $turret = $this->createMock(GameEquipment::class);
        $place = $this->createMock(Place::class);
        $place->method('getName')->willReturn(EquipmentEnum::TURRET_COMMAND);
        $turret->method('getId')->willReturn(1);
        $turret->method('getPlace')->willReturn($place);
        $chargeStatus = $this->createMock(ChargeStatus::class);
        $chargeStatus->method('getCharge')->willReturn(4);
        $turret->method('getStatusByName')->willReturn($chargeStatus);
        $player1 = $this->createMock(Player::class);
        $player1->method('getName')->willReturn(CharacterEnum::CHUN);
        $player1->method('getPlace')->willReturn($place);
        $player1->method('isAlive')->willReturn(true);
        $player2 = $this->createMock(Player::class);
        $player2->method('getName')->willReturn(CharacterEnum::ANDIE);
        $player2->method('getPlace')->willReturn($place);
        $player2->method('isAlive')->willReturn(true);
        $turretOccupiers = $this->createMock(PlayerCollection::class);
        $turretOccupiers->method('isEmpty')->willReturn(false);
        $place->method('getPlayers')->willReturn(new PlayerCollection([$player1, $player2]));
        $turretOccupiers->method('getPlayerAlive')->willReturn($turretOccupiers);

        $expected = [
            'id' => 1,
            'name' => EquipmentEnum::TURRET_COMMAND,
            'charges' => 4,
            'occupiers' => [CharacterEnum::CHUN, CharacterEnum::ANDIE],
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($turret));
    }

    public function testNormalizeReturnsNullOccupiersForEmptyCollection(): void
    {
        $turret = $this->createMock(GameEquipment::class);
        $place = $this->createMock(Place::class);
        $place->method('getName')->willReturn(EquipmentEnum::TURRET_COMMAND);
        $turret->method('getId')->willReturn(1);
        $turret->method('getPlace')->willReturn($place);
        $chargeStatus = $this->createMock(ChargeStatus::class);
        $chargeStatus->method('getCharge')->willReturn(4);
        $turret->method('getStatusByName')->willReturn($chargeStatus);
        $turretOccupiers = $this->createMock(PlayerCollection::class);
        $place->method('getPlayers')->willReturn(new PlayerCollection());
        $turretOccupiers->method('getPlayerAlive')->willReturn($turretOccupiers);

        $expected = [
            'id' => 1,
            'name' => EquipmentEnum::TURRET_COMMAND,
            'charges' => 4,
            'occupiers' => null,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($turret));
    }
}
