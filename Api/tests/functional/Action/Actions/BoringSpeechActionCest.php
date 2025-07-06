<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\BoringSpeech;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
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
final class BoringSpeechActionCest extends AbstractFunctionalTest
{
    private ActionConfig $boringSpeechActionConfig;
    private BoringSpeech $boringSpeechAction;
    private ChooseSkillUseCase $chooseSkillUseCase;
    private PlayerServiceInterface $playerService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->boringSpeechActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::BORING_SPEECH]);

        $this->boringSpeechAction = $I->grabService(BoringSpeech::class);

        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

        $this->playerService = $I->grabService(PlayerServiceInterface::class);
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
