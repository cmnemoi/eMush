<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class CreativeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Hit $hit;
    private ChooseSkillUseCase $chooseSkillUseCase;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HIT]);
        $this->hit = $I->grabService(Hit::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

        $this->givenChunIsCreative($I);
    }

    public function shouldNotGiveOneActionPointAfterSuccessfulAction(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(100);

        $this->givenChunHasActionPoints(1);

        $this->whenChunMakesAction();

        $this->thenChunShouldHaveActionPoints(0, $I);
    }

    public function shouldNotGiveActionPointsIfActionDoesNotConsumeActionPoints(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(0);

        $this->givenChunHasActionPoints(0);

        $this->givenActionCostIsZero();

        $this->whenChunMakesAction();

        $this->thenChunShouldHaveActionPoints(0, $I);
    }

    public function shouldGiveActionPointsAfterAFailedAction(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(0);

        $this->givenChunHasActionPoints(1);

        $this->whenChunMakesAction();

        $this->thenChunShouldHaveActionPoints(1, $I);
    }

    public function shouldPrintAPrivateLogWhenGivingActionPoints(FunctionalTester $I): void
    {
        $this->givenActionSuccessRateIs(0);

        $this->givenChunHasActionPoints(1);

        $this->whenChunMakesAction();

        $this->thenIShouldSeeAPrivateLog($I);
    }

    private function givenChunIsCreative(FunctionalTester $I): void
    {
        $this->chun->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::CREATIVE]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::CREATIVE, $this->chun));
    }

    private function givenActionSuccessRateIs(int $rate): void
    {
        $this->actionConfig->setSuccessRate($rate);
    }

    private function givenChunHasActionPoints(int $actionPoints): void
    {
        $this->chun->setActionPoint($actionPoints);
    }

    private function givenActionCostIsZero(): void
    {
        $this->actionConfig->setActionCost(0);
    }

    private function whenChunMakesAction(): void
    {
        $this->hit->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi
        );
        $this->hit->execute();
    }

    private function thenChunShouldHaveActionPoints(int $actionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($actionPoints, $this->chun->getActionPoint());
    }

    private function thenIShouldSeeAPrivateLog(FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Votre compétence **Créatif** a porté ses fruits...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::CREATIVE_WORKED,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }
}
