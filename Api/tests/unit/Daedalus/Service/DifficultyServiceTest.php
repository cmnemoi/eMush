<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Daedalus\Service;

use Codeception\Attribute\DataProvider;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Daedalus\Repository\InMemoryDaedalusRepository;
use Mush\Game\Enum\DifficultyEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\DifficultyService;
use Mush\Player\Factory\PlayerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DifficultyServiceTest extends TestCase
{
    private InMemoryDaedalusRepository $daedalusRepository;
    private DifficultyService $difficultyService;
    private Daedalus $daedalus;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->daedalusRepository = new InMemoryDaedalusRepository();
        $this->difficultyService = new DifficultyService($this->daedalusRepository);
        $this->daedalus = DaedalusFactory::createDaedalus();

        $this->givenDaedalusIsInProgress();
        $this->givenDifficultyModesAreConfigured();
    }

    public function testShouldUpdateBothHunterAndIncidentPoints(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(16);
        $initialHunterPoints = $this->daedalus->getHunterPoints();
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(7, $initialHunterPoints);
        $this->thenIncidentPointsShouldBeIncreasedBy(3, $initialIncidentPoints);
    }

    public function testShouldIncreaseHunterPointsOnDay1(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(16);
        $initialHunterPoints = $this->daedalus->getHunterPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(7, $initialHunterPoints);
    }

    public function testShouldIncreaseHunterPointsOnDay5(): void
    {
        $this->givenDaedalusIsOnDay(5);
        $this->givenDaedalusHasAlivePlayers(16);
        $initialHunterPoints = $this->daedalus->getHunterPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(12, $initialHunterPoints);
    }

    public function testShouldIncreaseHunterPointsInHardMode(): void
    {
        $this->givenDaedalusIsOnDay(8);
        $this->givenDaedalusHasAlivePlayers(16);
        $initialHunterPoints = $this->daedalus->getHunterPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(15, $initialHunterPoints);
    }

    public function testShouldIncreaseHunterPointsInVeryHardMode(): void
    {
        $this->givenDaedalusIsOnDay(12);
        $this->givenDaedalusHasAlivePlayers(16);
        $initialHunterPoints = $this->daedalus->getHunterPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(20, $initialHunterPoints);
    }

    public function testShouldIncreaseHunterPointsWithActivityOverload(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(16);
        $this->givenDaedalusHasDailyActionPointsSpent(56);
        $initialHunterPoints = $this->daedalus->getHunterPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(7, $initialHunterPoints);
    }

    /**
     * @dataProvider provideShouldHaveXIncidentPointsForYDayCases
     */
    public function testShouldHaveXIncidentPointsForYDay(int $day, int $expectedIncrease): void
    {
        $this->givenDaedalusIsOnDay($day);
        $this->givenDaedalusHasAlivePlayers(16);
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenIncidentPointsShouldBeIncreasedBy($expectedIncrease, $initialIncidentPoints);
    }

    public static function provideShouldHaveXIncidentPointsForYDayCases(): iterable
    {
        return [
            'day 1' => [
                1, 3,
            ],
            'day 2' => [
                2, 3,
            ],
            'day 3' => [
                3, 3,
            ],
            'day 4' => [
                4, 4,
            ],
            'day 5' => [
                5, 6,
            ],
            'day 6' => [
                6, 7,
            ],
            'day 7' => [
                7, 8,
            ],
            'day 8' => [
                8, 9,
            ],
            'day 9' => [
                9, 10,
            ],
            'day 10' => [
                10, 11,
            ],
            'day 11' => [
                11, 12,
            ],
            'day 12' => [
                12, 13,
            ],
            'day 13' => [
                13, 15,
            ],
            'day 14' => [
                14, 17,
            ],
            'day 15' => [
                15, 19,
            ],
            'day 16' => [
                16, 21,
            ],
            'day 17' => [
                17, 23,
            ],
            'day 18' => [
                18, 25,
            ],
            'day 19' => [
                19, 27,
            ],
        ];
    }

    public function testShouldIncreaseIncidentPointsWithActivityOverload(): void
    {
        $this->givenDaedalusIsOnDay(9);
        $this->givenDaedalusHasAlivePlayers(16);
        $this->givenDaedalusHasDailyActionPointsSpent(168);
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenIncidentPointsShouldBeIncreasedBy(15, $initialIncidentPoints);
    }

    public function testShouldNotUpdateIncidentPointsWhenDaedalusIsFilling(): void
    {
        $this->givenDaedalusIsFilling();
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(16);
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();
        $initialHunterPoints = $this->daedalus->getHunterPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenIncidentPointsShouldNotChange($initialIncidentPoints);
        $this->thenHunterPointsShouldBeIncreasedBy(7, $initialHunterPoints);
    }

    public function testShouldCalculateActivityOverloadAs1WhenNoAlivePlayers(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(0);
        $initialHunterPoints = $this->daedalus->getHunterPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(0, $initialHunterPoints);
    }

    public function testShouldCalculateActivityOverloadAs1WhenBelowThreshold(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(16);
        $this->givenDaedalusHasDailyActionPointsSpent(20);
        $initialHunterPoints = $this->daedalus->getHunterPoints();
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(7, $initialHunterPoints);
        $this->thenIncidentPointsShouldBeIncreasedBy(3, $initialIncidentPoints);
    }

    public function testShouldCalculateActivityOverloadWithHighActivity(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(16);
        $this->givenDaedalusHasDailyActionPointsSpent(42);
        $initialHunterPoints = $this->daedalus->getHunterPoints();
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(7, $initialHunterPoints);
        $this->thenIncidentPointsShouldBeIncreasedBy(3, $initialIncidentPoints);
    }

    public function testShouldSaveDaedalusAfterUpdate(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(16);

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenDaedalusShouldBeSaved();
    }

    public function testShouldHandleComplexScenarioWithVeryHardModeAndActivityOverload(): void
    {
        $this->givenDaedalusIsOnDay(15);
        $this->givenDaedalusHasAlivePlayers(8);
        $this->givenDaedalusHasDailyActionPointsSpent(42);
        $initialHunterPoints = $this->daedalus->getHunterPoints();
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(12, $initialHunterPoints);
        $this->thenIncidentPointsShouldBeIncreasedBy(10, $initialIncidentPoints);
    }

    private function givenDaedalusIsInProgress(): void
    {
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
    }

    private function givenDifficultyModesAreConfigured(): void
    {
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setDifficultyModes([
            DifficultyEnum::NORMAL => 1,
            DifficultyEnum::HARD => 5,
            DifficultyEnum::VERY_HARD => 10,
        ]);
    }

    private function givenDaedalusIsOnDay(int $day): void
    {
        $this->daedalus->setDay($day);
    }

    private function givenDaedalusHasAlivePlayers(int $count): void
    {
        for ($i = 0; $i < $count; ++$i) {
            PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        }
    }

    private function givenDaedalusHasDailyActionPointsSpent(int $points): void
    {
        $this->daedalus->setDailyActionSpent($points);
    }

    private function givenDaedalusIsFilling(): void
    {
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::STANDBY);
    }

    private function whenIUpdateDaedalusDifficulty(): void
    {
        $this->difficultyService->updateDaedalusDifficulty($this->daedalus);
    }

    private function thenHunterPointsShouldBeIncreasedBy(int $expectedIncrease, int $initialValue): void
    {
        self::assertEquals($initialValue + $expectedIncrease, $this->daedalus->getHunterPoints());
    }

    private function thenIncidentPointsShouldBeIncreasedBy(int $expectedIncrease, int $initialValue): void
    {
        self::assertEquals($initialValue + $expectedIncrease, $this->daedalus->getIncidentPoints());
    }

    private function thenIncidentPointsShouldNotChange(int $initialValue): void
    {
        self::assertEquals($initialValue, $this->daedalus->getIncidentPoints());
    }

    private function thenDaedalusShouldBeSaved(): void
    {
        $savedDaedalus = $this->daedalusRepository->findByIdOrThrow($this->daedalus->getId());
        self::assertSame($this->daedalus, $savedDaedalus);
    }
}
