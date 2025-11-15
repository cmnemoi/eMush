<?php

declare(strict_types=1);

namespace Mush\Achievement\Tests\Unit\ValueObject;

use Mush\Achievement\ValueObject\CycleCounts;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CycleCountsTest extends TestCase
{
    public function testIncrementForPlayer(): void
    {
        $cycleCount = new CycleCounts();
        $player = PlayerFactory::createPlayerByName(CharacterEnum::ANDIE);

        $result = $cycleCount->incrementForPlayer($player);

        self::assertEquals(1, $result->getForCharacter(CharacterEnum::ANDIE));
    }

    public function testIncrementForPlayerMultipleTimes(): void
    {
        $cycleCount = new CycleCounts();
        $player = PlayerFactory::createPlayerByName(CharacterEnum::ANDIE);

        $result = $cycleCount
            ->incrementForPlayer($player)
            ->incrementForPlayer($player, 2);

        self::assertEquals(3, $result->getForCharacter(CharacterEnum::ANDIE));
    }

    public function testIncrementForMushPlayer(): void
    {
        $cycleCount = new CycleCounts();
        $player = PlayerFactory::createPlayerByName(CharacterEnum::ANDIE);
        StatusFactory::createStatusByNameForHolder(PlayerStatusEnum::MUSH, $player);

        $result = $cycleCount->incrementForPlayer($player);

        self::assertEquals(1, $result->getForCharacter(CharacterEnum::MUSH));
    }

    public function testFromArrayAndToArray(): void
    {
        $data = [
            CharacterEnum::ANDIE => 5,
            CharacterEnum::CHAO => 3,
        ];

        $cycleCount = CycleCounts::fromArray($data);
        self::assertEquals(5, $cycleCount->getForCharacter(CharacterEnum::ANDIE));
        self::assertEquals(3, $cycleCount->getForCharacter(CharacterEnum::CHAO));
    }

    public function testShouldReturnCycleCountsForCharacter(): void
    {
        $data = [
            CharacterEnum::ANDIE => 5,
            CharacterEnum::CHAO => 3,
        ];

        $cycleCount = CycleCounts::fromArray($data);
        self::assertEquals(5, $cycleCount->getForCharacter(CharacterEnum::ANDIE));
        self::assertEquals(3, $cycleCount->getForCharacter(CharacterEnum::CHAO));
        self::assertEquals(0, $cycleCount->getForCharacter(CharacterEnum::KUAN_TI));
    }
}
