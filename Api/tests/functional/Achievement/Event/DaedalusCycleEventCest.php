<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Achievement\Event;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusCycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private StatisticRepositoryInterface $statisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    #[DataProvider('dayReachedDataProvider')]
    public function shouldGiveDayReachedStatisticToAlivePlayersOnC1(FunctionalTester $I, Example $example): void
    {
        $day = $example['day'];
        $statistic = StatisticEnum::from($example['statistic']);

        $this->givenExtraPlace($I);
        $this->givenDaedalusIsAtDayMinusOneCycleEight($day);
        $this->whenNewCycle();
        $this->thenAllAlivePlayersHaveStatistic($statistic, $I);
    }

    #[DataProvider('dayReachedDataProvider')]
    public function shouldNotGiveDayReachedStatisticIfNotNewDay(FunctionalTester $I, Example $example): void
    {
        $this->givenExtraPlace($I);
        $this->givenDaedalusIsAtDayCycleOne($example['day']);
        $this->whenNewCycle();
        $this->thenAlivePlayersDoNotHaveStatistic(StatisticEnum::from($example['statistic']), $I);
    }

    public function dayReachedDataProvider(): array
    {
        return [
            'Day 5' => [
                'day' => 5,
                'statistic' => StatisticEnum::DAY_5_REACHED->value,
            ],
            'Day 10' => [
                'day' => 10,
                'statistic' => StatisticEnum::DAY_10_REACHED->value,
            ],
            'Day 15' => [
                'day' => 15,
                'statistic' => StatisticEnum::DAY_15_REACHED->value,
            ],
            'Day 20' => [
                'day' => 20,
                'statistic' => StatisticEnum::DAY_20_REACHED->value,
            ],
            'Day 30' => [
                'day' => 30,
                'statistic' => StatisticEnum::DAY_30_REACHED->value,
            ],
        ];
    }

    private function givenExtraPlace(FunctionalTester $I): void
    {
        $this->createExtraPlace(RoomEnum::FRONT_STORAGE, $I, $this->daedalus);
    }

    private function givenDaedalusIsAtDayMinusOneCycleEight(int $day): void
    {
        $this->daedalus->setDay($day - 1)->setCycle(8);
    }

    private function givenDaedalusIsAtDayCycleOne(int $day): void
    {
        $this->daedalus->setDay($day)->setCycle(1);
    }

    private function whenNewCycle(): void
    {
        $this->eventService->callEvent(
            event: new DaedalusCycleEvent(
                daedalus: $this->daedalus,
                tags: [EventEnum::NEW_CYCLE],
                time: new \DateTime(),
            ),
            name: DaedalusCycleEvent::DAEDALUS_NEW_CYCLE,
        );
    }

    private function thenAllAlivePlayersHaveStatistic(StatisticEnum $statistic, FunctionalTester $I): void
    {
        foreach ($this->players as $player) {
            $I->assertEquals(
                expected: 1,
                actual: $this->statisticRepository->findByNameAndUserIdOrNull(
                    $statistic,
                    $player->getUser()->getId()
                )?->getCount(),
                message: "{$player->getLogName()} should have {$statistic->value} statistic"
            );
        }
    }

    private function thenAlivePlayersDoNotHaveStatistic(StatisticEnum $statistic, FunctionalTester $I): void
    {
        foreach ($this->players as $player) {
            $I->assertNull(
                $this->statisticRepository->findByNameAndUserIdOrNull(
                    $statistic,
                    $player->getUser()->getId()
                )?->getId(),
            );
        }
    }
}
