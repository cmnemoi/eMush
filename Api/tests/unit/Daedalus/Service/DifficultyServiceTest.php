<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Daedalus\Service;

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
        $this->givenDaedalusHasAlivePlayers(4);
        $initialHunterPoints = $this->daedalus->getHunterPoints();
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(7, $initialHunterPoints);
        $this->thenIncidentPointsShouldBeIncreasedBy(1, $initialIncidentPoints);
    }

    public function testShouldIncreaseHunterPointsOnDay1(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(4);
        $initialHunterPoints = $this->daedalus->getHunterPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(7, $initialHunterPoints);
    }

    public function testShouldIncreaseHunterPointsOnDay5(): void
    {
        $this->givenDaedalusIsOnDay(5);
        $this->givenDaedalusHasAlivePlayers(4);
        $initialHunterPoints = $this->daedalus->getHunterPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(11, $initialHunterPoints);
    }

    public function testShouldIncreaseHunterPointsInHardMode(): void
    {
        $this->givenDaedalusIsOnDay(8);
        $this->givenDaedalusHasAlivePlayers(4);
        $initialHunterPoints = $this->daedalus->getHunterPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(15, $initialHunterPoints);
    }

    public function testShouldIncreaseHunterPointsInVeryHardMode(): void
    {
        $this->givenDaedalusIsOnDay(12);
        $this->givenDaedalusHasAlivePlayers(4);
        $initialHunterPoints = $this->daedalus->getHunterPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(20, $initialHunterPoints);
    }

    public function testShouldIncreaseHunterPointsWithActivityOverload(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(4);
        $this->givenDaedalusHasDailyActionPointsSpent(56);
        $initialHunterPoints = $this->daedalus->getHunterPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(14, $initialHunterPoints);
    }

    public function testShouldIncreaseIncidentPointsByDayOnDay1(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(4);
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenIncidentPointsShouldBeIncreasedBy(1, $initialIncidentPoints);
    }

    public function testShouldIncreaseIncidentPointsByDayOnDay2(): void
    {
        $this->givenDaedalusIsOnDay(2);
        $this->givenDaedalusHasAlivePlayers(4);
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenIncidentPointsShouldBeIncreasedBy(2, $initialIncidentPoints);
    }

    public function testShouldCapIncidentPointsAfterDay2(): void
    {
        $this->givenDaedalusIsOnDay(5);
        $this->givenDaedalusHasAlivePlayers(4);
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenIncidentPointsShouldBeIncreasedBy(1, $initialIncidentPoints);
    }

    public function testShouldIncreaseIncidentPointsInHardMode(): void
    {
        $this->givenDaedalusIsOnDay(8);
        $this->givenDaedalusHasAlivePlayers(4);
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenIncidentPointsShouldBeIncreasedBy(2, $initialIncidentPoints);
    }

    public function testShouldIncreaseIncidentPointsInHardModeDay10(): void
    {
        $this->givenDaedalusIsOnDay(10);
        $this->givenDaedalusHasAlivePlayers(4);
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenIncidentPointsShouldBeIncreasedBy(4, $initialIncidentPoints);
    }

    public function testShouldIncreaseIncidentPointsInVeryHardMode(): void
    {
        $this->givenDaedalusIsOnDay(12);
        $this->givenDaedalusHasAlivePlayers(4);
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenIncidentPointsShouldBeIncreasedBy(8, $initialIncidentPoints);
    }

    public function testShouldIncreaseIncidentPointsWithActivityOverload(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(4);
        $this->givenDaedalusHasDailyActionPointsSpent(42);
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenIncidentPointsShouldBeIncreasedBy(2, $initialIncidentPoints);
    }

    public function testShouldNotUpdateIncidentPointsWhenDaedalusIsFilling(): void
    {
        $this->givenDaedalusIsFilling();
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(4);
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

        $this->thenHunterPointsShouldBeIncreasedBy(7, $initialHunterPoints);
    }

    public function testShouldCalculateActivityOverloadAs1WhenBelowThreshold(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(4);
        $this->givenDaedalusHasDailyActionPointsSpent(20);
        $initialHunterPoints = $this->daedalus->getHunterPoints();
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(7, $initialHunterPoints);
        $this->thenIncidentPointsShouldBeIncreasedBy(1, $initialIncidentPoints);
    }

    public function testShouldCalculateActivityOverloadWithHighActivity(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(2);
        $this->givenDaedalusHasDailyActionPointsSpent(42);
        $initialHunterPoints = $this->daedalus->getHunterPoints();
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(21, $initialHunterPoints);
        $this->thenIncidentPointsShouldBeIncreasedBy(3, $initialIncidentPoints);
    }

    public function testShouldSaveDaedalusAfterUpdate(): void
    {
        $this->givenDaedalusIsOnDay(1);
        $this->givenDaedalusHasAlivePlayers(4);

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenDaedalusShouldBeSaved();
    }

    public function testShouldHandleComplexScenarioWithVeryHardModeAndActivityOverload(): void
    {
        $this->givenDaedalusIsOnDay(15);
        $this->givenDaedalusHasAlivePlayers(3);
        $this->givenDaedalusHasDailyActionPointsSpent(42);
        $initialHunterPoints = $this->daedalus->getHunterPoints();
        $initialIncidentPoints = $this->daedalus->getIncidentPoints();

        $this->whenIUpdateDaedalusDifficulty();

        $this->thenHunterPointsShouldBeIncreasedBy(46, $initialHunterPoints);
        $this->thenIncidentPointsShouldBeIncreasedBy(28, $initialIncidentPoints);
    }

    private function givenDaedalusIsInProgress(): void
    {
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
    }

    private function givenDifficultyModesAreConfigured(): void
    {
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setDifficultyModes([
            DifficultyEnum::NORMAL => 1,
            DifficultyEnum::HARD => 8,
            DifficultyEnum::VERY_HARD => 12,
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
