<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\BoringSpeech;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class BoringSpeechActionCest extends AbstractFunctionalTest
{
    private ActionConfig $boringSpeechActionConfig;
    private BoringSpeech $boringSpeechAction;
    private PlayerServiceInterface $playerService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->boringSpeechActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::BORING_SPEECH]);

        $this->boringSpeechAction = $I->grabService(BoringSpeech::class);

        $this->playerService = $I->grabService(PlayerServiceInterface::class);

        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
    }

    public function testBoringSpeech(FunctionalTester $I)
    {
        $this->givenChunIsMotivator($I);

        $this->givenKuanTiHasMovementPoints(0);
        $this->givenChunHasMovementPoints(0);

        $this->whenChunGivesBoringSpeech();

        $this->thenKuanTiShouldHaveMovementPoints(3, $I);
        $this->thenChunShouldHaveMovementPoints(0, $I);

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->chun->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->chun->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::BORING_SPEECH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertEquals($this->boringSpeechAction->cannotExecuteReason(), ActionImpossibleCauseEnum::ALREADY_DID_BORING_SPEECH);
    }

    public function shouldNotGiveMovementPointToDeadPlayer(FunctionalTester $I): void
    {
        $this->givenChunIsMotivator($I);

        $this->givenKuanTiHasMovementPoints(10);

        $this->givenKuanTiIsDead();

        $this->whenChunGivesBoringSpeech();

        $this->thenKuanTiShouldHaveMovementPoints(10, $I);
    }

    public function shouldGivePoliticianPendingStatistic(FunctionalTester $I): void
    {
        $this->givenChunIsMotivator($I);

        for ($i = 0; $i < 7; ++$i) {
            $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        }

        $this->whenChunGivesBoringSpeech();

        $I->assertEquals(
            expected: 1,
            actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::POLITICIAN,
                $this->chun->getUser()->getId(),
                $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
            )?->getCount(),
            message: 'Politician pendingstatistic should be incremented'
        );
    }

    public function shouldNotGivePoliticianPendingStatisticIfLessThan8Players(FunctionalTester $I): void
    {
        $this->givenChunIsMotivator($I);

        for ($i = 0; $i < 5; ++$i) {
            $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        }

        $this->whenChunGivesBoringSpeech();

        $I->assertNull(
            $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::POLITICIAN,
                $this->chun->getUser()->getId(),
                $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId(),
            )?->getId(),
            message: 'Politician pending statistic should not be incremented'
        );
    }

    private function givenChunIsMotivator(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::MOTIVATOR, $I);
    }

    private function givenKuanTiHasMovementPoints(int $points): void
    {
        $this->kuanTi->setMovementPoint($points);
    }

    private function givenChunHasMovementPoints(int $points): void
    {
        $this->chun->setMovementPoint($points);
    }

    private function givenKuanTiIsDead(): void
    {
        $this->playerService->killPlayer(player: $this->kuanTi, endReason: EndCauseEnum::ABANDONED);
    }

    private function whenChunGivesBoringSpeech(): void
    {
        $this->boringSpeechAction->loadParameters($this->boringSpeechActionConfig, $this->chun, $this->chun);
        $this->boringSpeechAction->execute();
    }

    private function thenKuanTiShouldHaveMovementPoints(int $expectedPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedPoints, $this->kuanTi->getMovementPoint());
    }

    private function thenChunShouldHaveMovementPoints(int $expectedPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedPoints, $this->chun->getMovementPoint());
    }
}
