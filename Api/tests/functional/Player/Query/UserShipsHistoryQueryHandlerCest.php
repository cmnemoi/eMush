<?php

declare(strict_types=1);

namespace Mush\Player\Query;

use Mush\Game\Enum\TitleEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\ViewModel\UserShipsHistoryViewModel;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

/**
 * @internal
 */
final class UserShipsHistoryQueryHandlerCest extends AbstractFunctionalTest
{
    private UserShipsHistoryQueryHandler $queryHandler;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->queryHandler = $I->grabService(UserShipsHistoryQueryHandler::class);
    }

    public function shouldBeEmptyIfDaedalusIsNotFinished(FunctionalTester $I): void
    {
        // given
        $closedDaedalus = $this->daedalus->getDaedalusInfo()->getClosedDaedalus();
        $this->kuanTi->getPlayerInfo()->getClosedPlayer()->setClosedDaedalus($closedDaedalus);
        $I->haveInRepository($this->kuanTi->getPlayerInfo()->getClosedPlayer());

        // when
        $result = $this->whenGettingUserShipsHistory($this->kuanTi->getUser());

        // then
        $I->assertCount(0, $result['data']);
    }

    public function shouldBeEmptyIfDaedalusCheated(FunctionalTester $I): void
    {
        // given
        $closedDaedalus = $this->daedalus->getDaedalusInfo()->getClosedDaedalus();
        $closedDaedalus->setFinishedAt(new \DateTime('now'))->markAsCheater();
        $this->kuanTi->getPlayerInfo()->getClosedPlayer()->setClosedDaedalus($closedDaedalus);
        $I->haveInRepository($this->kuanTi->getPlayerInfo()->getClosedPlayer());

        // when
        $result = $this->whenGettingUserShipsHistory($this->kuanTi->getUser());

        // then
        $I->assertCount(0, $result['data']);
    }

    public function shouldReturnShipsHistoryIfUserHasPastGames(FunctionalTester $I): void
    {
        // given
        $this->daedalus->getDaedalusInfo()->getDaedalusStatistics()->incrementExplorationsStarted(1);
        $this->daedalus->getDaedalusInfo()->getDaedalusStatistics()->incrementPlanetsFound(2);
        $this->daedalus->getDaedalusInfo()->getDaedalusProjectsStatistics()->addCompletedProject(
            $this->daedalus->getProjectByName(ProjectName::ANTISPORE_GAS)
        );
        $this->daedalus->getDaedalusInfo()->getDaedalusProjectsStatistics()->addCompletedProject(
            $this->daedalus->getProjectByName(ProjectName::PLASMA_SHIELD)
        );
        $this->kuanTi->setTriumph(150);
        $this->kuanTi->addTitle(TitleEnum::COMMANDER);
        $this->kuanTi->getPlayerInfo()->getClosedPlayer()->setEndCause(EndCauseEnum::ABANDONED);
        $this->kuanTi->getPlayerInfo()->getClosedPlayer()->setIsMush(true);

        $closedDaedalus = $this->daedalus->getDaedalusInfo()->getClosedDaedalus();
        $closedDaedalus->setFinishedAt(new \DateTime('now'));
        $this->kuanTi->getPlayerInfo()->getClosedPlayer()->setClosedDaedalus($closedDaedalus);
        $I->haveInRepository($this->kuanTi->getPlayerInfo()->getClosedPlayer());

        // when
        $result = $this->whenGettingUserShipsHistory($this->kuanTi->getUser());

        // then
        $I->assertEquals(
            expected: [
                'data' => [
                    new UserShipsHistoryViewModel(
                        characterBody: 'kuan_ti',
                        characterName: 'kuan_ti',
                        daysSurvived: 0,
                        nbExplorations: 1,
                        nbNeronProjects: 1,
                        nbResearchProjects: 1,
                        nbScannedPlanets: 2,
                        titles: [TitleEnum::COMMANDER],
                        triumph: 150,
                        endCause: EndCauseEnum::ABANDONED,
                        daedalusId: $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
                        playerWasMush: true,
                    ),
                ],
                'totalItems' => 1,
            ],
            actual: $result
        );
    }

    private function whenGettingUserShipsHistory(User $user): array
    {
        $query = new UserShipsHistoryQuery(
            userId: $user->getUserId(),
            page: 1,
            itemsPerPage: 10,
            language: 'fr'
        );

        return $this->queryHandler->execute($query);
    }
}
