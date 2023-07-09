<?php

declare(strict_types=1);

namespace Tests\Mush\Equipment\Normalizer;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Normalizer\SpaceBattlePatrolShipNormalizer;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use PHPUnit\Framework\TestCase;

class SpaceBattlePatrolShipNormalizerTest extends TestCase
{
    private SpaceBattlePatrolShipNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new SpaceBattlePatrolShipNormalizer();
    }

    public function testSupportsNormalizationReturnsTrueForPatrolShip(): void
    {
        $patrolShip = $this->createMock(GameEquipment::class);
        $patrolShip->method('getName')->willReturn(EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS);

        $this->assertTrue($this->normalizer->supportsNormalization($patrolShip));
    }

    public function testSupportsNormalizationReturnsFalseForNonPatrolShip(): void
    {
        $shield = $this->createMock(GameEquipment::class);
        $shield->method('getName')->willReturn(EquipmentEnum::DOOR);

        $this->assertFalse($this->normalizer->supportsNormalization($shield));
    }

    public function testNormalizeReturnsExpectedArray(): void
    {
        $patrolShip = $this->createMock(GameEquipment::class);
        $patrolShipArmor = $this->createMock(ChargeStatus::class);
        $patrolShipArmor->method('getCharge')->willReturn(10);
        $patrolShipCharges = $this->createMock(ChargeStatus::class);
        $patrolShipCharges->method('getCharge')->willReturn(10);
        $patrolShipPilot = $this->createMock(Player::class);
        $patrolShipPilot->method('getName')->willReturn(CharacterEnum::CHUN);
        $place = $this->createMock(Place::class);
        $placePlayers = $this->createMock(PlayerCollection::class);
        $place->method('getPlayers')->willReturn($placePlayers);
        $placePlayers->method('getPlayerAlive')->willReturn(new PlayerCollection([$patrolShipPilot]));
        $patrolShip->method('getId')->willReturn(1);
        $patrolShip->method('getName')->willReturn(EquipmentEnum::PATROL_SHIP);
        $patrolShip->method('getStatusByName')->will($this->returnValueMap([
            [EquipmentStatusEnum::PATROL_SHIP_ARMOR, $patrolShipArmor],
            [EquipmentStatusEnum::ELECTRIC_CHARGES, $patrolShipCharges],
        ]));
        $patrolShip->method('getPlace')->willReturn($place);

        $expected = [
            'id' => 1,
            'name' => EquipmentEnum::PATROL_SHIP,
            'armor' => 10,
            'charges' => 10,
            'pilot' => CharacterEnum::CHUN,
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($patrolShip));
    }
}
