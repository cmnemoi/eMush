<?php

declare(strict_types=1);

namespace Mush\Test\Functional\Achievement\Event;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectFinishedEventCest extends AbstractFunctionalTest
{
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
    }

    public function shouldGivePilgredIsBackPendingStatisticToAlivePlayers(FunctionalTester $I): void
    {
        // when PILGRED is finished
        $this->finishProject(
            project: $this->daedalus->getPilgred(),
            author: $this->player,
            I: $I
        );

        // then all alive players should have PILGRED_IS_BACK pending statistic
        foreach ($this->players as $player) {
            $I->assertEquals(
                expected: [
                    'name' => StatisticEnum::PILGRED_IS_BACK,
                    'count' => 1,
                    'userId' => $player->getUser()->getId(),
                    'closedDaedalusId' => $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
                    'isRare' => true,
                ],
                actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                    StatisticEnum::PILGRED_IS_BACK,
                    $player->getUser()->getId(),
                    $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
                )?->toArray(),
                message: "{$player->getLogName()} should have PILGRED_IS_BACK statistic"
            );
        }
    }

    public function shouldGivePlasmaShieldStatisticToAlivePlayers(FunctionalTester $I): void
    {
        // when PLASMA_SHIELD is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD),
            author: $this->player,
            I: $I
        );

        // then all alive players should have PLASMA_SHIELD statistic
        foreach ($this->players as $player) {
            $I->assertEquals(
                expected: 1,
                actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                    StatisticEnum::PLASMA_SHIELD,
                    $player->getUser()->getId(),
                    $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
                )?->getCount(),
                message: "{$player->getLogName()} should have PLASMA_SHIELD statistic"
            );
        }
    }
}
