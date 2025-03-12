<?php

declare(strict_types=1);

namespace Mush\Daedalus\ValueObject;

use Mush\Daedalus\Factory\DaedalusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GameDateTest extends TestCase
{
    /**
     * @dataProvider providePreviousCases
     */
    public function testPrevious(GameDate $currentGameDate, GameDate $expectedPreviousGameDate): void
    {
        $previous = $currentGameDate->previous();

        self::assertTrue($previous->equals($expectedPreviousGameDate));
    }

    /**
     * @dataProvider provideLessThanOrEqualCases
     */
    public function testLessThanOrEqual(GameDate $currentGameDate, GameDate $otherGameDate, bool $expectedResult): void
    {
        self::assertEquals(
            $expectedResult,
            $currentGameDate->lessThanOrEqual($otherGameDate)
        );
    }

    /**
     * @dataProvider provideCyclesAgoCases
     */
    public function testCyclesAgo(GameDate $currentGameDate, int $delta, GameDate $expectedGameDate): void
    {
        self::assertEquals(
            $expectedGameDate,
            $currentGameDate->cyclesAgo($delta)
        );
    }

    public static function providePreviousCases(): iterable
    {
        $daedalus = DaedalusFactory::createDaedalus();

        return [
            'Day 2 Cycle 2 previous date should be Day 2 Cycle 1' => [
                new GameDate($daedalus, day: 2, cycle: 2),
                new GameDate($daedalus, day: 2, cycle: 1),
            ],
            'Day 2 Cycle 1 previous date should be Day 1 Cycle 8' => [
                new GameDate($daedalus, day: 2, cycle: 1),
                new GameDate($daedalus, day: 1, cycle: 8),
            ],
        ];
    }

    public static function provideLessThanOrEqualCases(): iterable
    {
        $daedalus = DaedalusFactory::createDaedalus();

        return [
            'Day 1 Cycle 2 is less than or equal to Day 1 Cycle 3' => [
                new GameDate($daedalus, day: 2, cycle: 2),
                new GameDate($daedalus, day: 2, cycle: 3),
                true,
            ],
            'Day 1 Cycle 8 is less than or equal to Day 2 Cycle 1' => [
                new GameDate($daedalus, day: 2, cycle: 2),
                new GameDate($daedalus, day: 3, cycle: 1),
                true,
            ],
            'Day 1 Cycle 2 is not less than or equal to Day 1 Cycle 1' => [
                new GameDate($daedalus, day: 1, cycle: 8),
                new GameDate($daedalus, day: 1, cycle: 7),
                false,
            ],
            'Day 1 Cycle 1 is less than or equal to Day 1 Cycle 8' => [
                new GameDate($daedalus, day: 1, cycle: 1),
                new GameDate($daedalus, day: 1, cycle: 8),
                true,
            ],
        ];
    }

    public static function provideCyclesAgoCases(): iterable
    {
        $daedalus = DaedalusFactory::createDaedalus();

        return [
            '0 cycle ago from Day 1 Cycle 1 should be Day 1 Cycle 1' => [
                new GameDate($daedalus, day: 1, cycle: 1),
                0,
                new GameDate($daedalus, day: 1, cycle: 1),
            ],
            '1 cycle ago from Day 1 Cycle 2 should be Day 1 Cycle 1' => [
                new GameDate($daedalus, day: 1, cycle: 2),
                1,
                new GameDate($daedalus, day: 1, cycle: 1),
            ],
            '1 cycle ago from Day 2 Cycle 1 should be Day 1 Cycle 8' => [
                new GameDate($daedalus, day: 2, cycle: 1),
                1,
                new GameDate($daedalus, day: 1, cycle: 8),
            ],
            '8 cycles ago from Day 1 Cycle 8 should be Day 1 Cycle 1' => [
                new GameDate($daedalus, day: 1, cycle: 8),
                8,
                new GameDate($daedalus, day: 1, cycle: 1),
            ],
            '8 cycles ago from Day 2 Cycle 8 should be Day 1 Cycle 8' => [
                new GameDate($daedalus, day: 2, cycle: 8),
                8,
                new GameDate($daedalus, day: 1, cycle: 8),
            ],
            '9 cycles ago from Day 2 Cycle 8 should be Day 1 Cycle 7' => [
                new GameDate($daedalus, day: 2, cycle: 8),
                9,
                new GameDate($daedalus, day: 1, cycle: 7),
            ],
            '16 cycles ago from Day 2 Cycle 8 should be Day 1 Cycle 1' => [
                new GameDate($daedalus, day: 2, cycle: 8),
                16,
                new GameDate($daedalus, day: 1, cycle: 1),
            ],
        ];
    }
}
