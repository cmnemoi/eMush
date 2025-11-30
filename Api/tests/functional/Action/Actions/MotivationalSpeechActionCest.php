<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\MotivationalSpeech;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class MotivationalSpeechActionCest extends AbstractFunctionalTest
{
    private MotivationalSpeech $motivationalSpeechAction;
    private ActionConfig $action;

    private ChooseSkillUseCase $chooseSkillUseCase;
    private PlayerServiceInterface $playerService;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->motivationalSpeechAction = $I->grabService(MotivationalSpeech::class);
        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::MOTIVATIONAL_SPEECH]);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);
    }

    public function testMotivationalSpeech(FunctionalTester $I)
    {
        $this->givenChunIsLeader($I);

        $this->givenKuanTiHasMoralePoints(1);

        $this->whenChunGivesMotivationalSpeech();

        $this->thenKuanTiShouldHaveMoralePoints(3, $I);

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::MOTIVATIONAL_SPEECH,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function shouldNotGiveMoraleToDeadPlayer(FunctionalTester $I): void
    {
        $this->givenChunIsLeader($I);

        $this->givenKuanTiHasMoralePoints(10);

        $this->givenKuanTiIsDead();

        $this->whenChunGivesMotivationalSpeech();

        $this->thenKuanTiShouldHaveMoralePoints(10, $I);
    }

    public function shouldGivePoliticianPendingStatistic(FunctionalTester $I): void
    {
        $this->givenChunIsLeader($I);

        for ($i = 0; $i < 7; ++$i) {
            $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        }

        $this->whenChunGivesMotivationalSpeech();

        $I->assertEquals(
            expected: 1,
            actual: $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::POLITICIAN,
                $this->chun->getUser()->getId(),
                $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
            )?->getCount(),
            message: 'Politician pending statistic should be incremented'
        );
    }

    public function shouldNotGivePoliticianStatisticIfLessThan8Players(FunctionalTester $I): void
    {
        $this->givenChunIsLeader($I);

        for ($i = 0; $i < 5; ++$i) {
            $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        }

        $this->whenChunGivesMotivationalSpeech();

        $I->assertNull(
            $this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(
                StatisticEnum::POLITICIAN,
                $this->chun->getUser()->getId(),
                $this->daedalus->getDaedalusInfo()->getClosedDaedalus()->getId()
            )?->getId(),
            message: 'Politician statistic should not be incremented'
        );
    }

    private function givenChunIsLeader(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::LEADER, $I);
    }

    private function givenKuanTiHasMoralePoints(int $points): void
    {
        $this->kuanTi->setMoralPoint($points);
    }

    private function givenKuanTiIsDead(): void
    {
        $this->playerService->killPlayer(player: $this->kuanTi, endReason: EndCauseEnum::ABANDONED);
    }

    private function whenChunGivesMotivationalSpeech(): void
    {
        $this->motivationalSpeechAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->chun,
            player: $this->chun
        );
        $this->motivationalSpeechAction->execute();
    }

    private function thenKuanTiShouldHaveMoralePoints(int $expectedPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedPoints, $this->kuanTi->getMoralPoint());
    }
}
