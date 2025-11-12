<?php

declare(strict_types=1);

namespace Mush\Test\Functional\Achievement\Event;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectFinishedEventCest extends AbstractFunctionalTest
{
    private StatisticRepositoryInterface $statisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->statisticRepository = $I->grabService(StatisticRepositoryInterface::class);
    }

    public function shouldGivePilgredIsBackStatisticToAlivePlayers(FunctionalTester $I): void
    {
        // when PILGRED is finished
        $this->finishProject(
            project: $this->daedalus->getPilgred(),
            author: $this->player,
            I: $I
        );

        // then all alive players should have PILGRED_IS_BACK statistic
        foreach ($this->players as $player) {
            $I->assertEquals(
                expected: [
                    'name' => StatisticEnum::PILGRED_IS_BACK,
                    'count' => 1,
                    'isRare' => true,
                    'userId' => $player->getUser()->getId(),
                ],
                actual: $this->statisticRepository->findByNameAndUserIdOrNull(
                    StatisticEnum::PILGRED_IS_BACK,
                    $player->getUser()->getId()
                )?->toArray(),
                message: "{$player->getLogName()} should have PILGRED_IS_BACK statistic"
            );
        }
    }
}
