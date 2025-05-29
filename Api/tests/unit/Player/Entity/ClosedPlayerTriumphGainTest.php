<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\Entity;

use Mush\Player\Entity\ClosedPlayer;
use Mush\Triumph\Enum\TriumphEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ClosedPlayerTriumphGainTest extends TestCase
{
    public function testShouldAddFirstTriumphGain(): void
    {
        $closedPlayer = $this->givenClosedPlayer();

        $this->whenRecordTriumphGain($closedPlayer, TriumphEnum::CYCLE_HUMAN, 1);

        $this->thenTriumphGainsShouldContainExactly($closedPlayer, [
            [
                'triumphKey' => TriumphEnum::CYCLE_HUMAN,
                'value' => 1,
                'count' => 1,
            ],
        ]);
    }

    public function testShouldIncrementCountForSameTriumphGain(): void
    {
        $closedPlayer = $this->givenClosedPlayer();

        $this->whenRecordTriumphGain($closedPlayer, TriumphEnum::CYCLE_HUMAN, 1);
        $this->whenRecordTriumphGain($closedPlayer, TriumphEnum::CYCLE_HUMAN, 1);

        $this->thenTriumphGainsShouldContainExactly($closedPlayer, [
            [
                'triumphKey' => TriumphEnum::CYCLE_HUMAN,
                'value' => 1,
                'count' => 2,
            ],
        ]);
    }

    public function testShouldAddTriumphGainWithSameKeyButDifferentValue(): void
    {
        $closedPlayer = $this->givenClosedPlayer();

        $this->whenRecordTriumphGain($closedPlayer, TriumphEnum::CYCLE_HUMAN, 1);
        $this->whenRecordTriumphGain($closedPlayer, TriumphEnum::CYCLE_HUMAN, 2);

        $this->thenTriumphGainsShouldContainExactly($closedPlayer, [
            [
                'triumphKey' => TriumphEnum::CYCLE_HUMAN,
                'value' => 1,
                'count' => 1,
            ],
            [
                'triumphKey' => TriumphEnum::CYCLE_HUMAN,
                'value' => 2,
                'count' => 1,
            ],
        ]);
    }

    private function givenClosedPlayer(): ClosedPlayer
    {
        return new ClosedPlayer();
    }

    private function whenRecordTriumphGain(ClosedPlayer $closedPlayer, TriumphEnum $triumph, int $count): void
    {
        $closedPlayer->recordTriumphGain($triumph, $count);
    }

    private function thenTriumphGainsShouldContainExactly(ClosedPlayer $closedPlayer, array $expectedGains): void
    {
        $gains = $closedPlayer->getTriumphGains();
        self::assertCount(\count($expectedGains), $gains);
        foreach ($expectedGains as $index => $expectedGain) {
            $gain = $gains->get($index);
            self::assertSame($expectedGain['triumphKey'], $gain->getTriumphKey(), "TriumphEnum mismatch at index {$index}");
            self::assertSame($expectedGain['value'], $gain->getValue(), "Value mismatch at index {$index}");
            self::assertSame($expectedGain['count'], $gain->getCount(), "Count mismatch at index {$index}");
        }
    }
}
