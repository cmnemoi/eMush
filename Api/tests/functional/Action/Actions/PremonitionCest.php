<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Flirt;
use Mush\Action\Actions\Premonition;
use Mush\Action\Actions\Search;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class PremonitionCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Premonition $premonition;

    private Flirt $flirt;
    private Search $search;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PREMONITION]);
        $this->premonition = $I->grabService(Premonition::class);

        $this->flirt = $I->grabService(Flirt::class);
        $this->search = $I->grabService(Search::class);

        $this->addSkillToPlayer(SkillEnum::PRESENTIMENT, $I);
    }

    public function shouldNotBeExecutableWithOneMoralePoint(FunctionalTester $I): void
    {
        $this->givenChunHasMoralePoints(1);

        $this->whenChunTriesToUsePremonitionOnKuanTi();

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::PREMONITION_INSUFFICIENT_MORALE,
            I: $I
        );
    }

    public function shouldCostsOneMoralePoint(FunctionalTester $I): void
    {
        $this->givenChunHasMoralePoints(10);

        $this->whenChunUsesPremonitionOnKuanTi();

        $this->thenChunShouldHaveMoralePoints(9, $I);
    }

    public function shouldCreatePrivateLogForPlayerWithTargetPlayerLastAction(FunctionalTester $I): void
    {
        $this->givenKuanTiExecutedSearchAction($I);
        $this->givenKuanTiExecutedFlirtAction($I);

        $this->whenChunUsesPremonitionOnKuanTi();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous vous concentrez... Votre tête tourne... Vous revoyez **Kuan Ti** faire quelque chose... Sa dernière action est **Fleureter**.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::PREMONITION_ACTION,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    public function shouldCreatePrivateLogForPlayerWhenTargetPlayerDidNotMakeAction(FunctionalTester $I): void
    {
        $this->whenChunUsesPremonitionOnKuanTi();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "Vous vous concentrez mais rien ne vient... Cette personne n'a sans doute rien fait depuis son réveil.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::PREMONITION_ACTION,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    private function givenChunHasMoralePoints(int $moralePoints): void
    {
        $this->chun->setMoralPoint($moralePoints);
    }

    private function givenKuanTiExecutedSearchAction(FunctionalTester $I): void
    {
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SEARCH]);
        $this->search->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
        $this->search->execute();
    }

    private function givenKuanTiExecutedFlirtAction(FunctionalTester $I): void
    {
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::FLIRT]);
        $this->flirt->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->chun,
        );
        $this->flirt->execute();
    }

    private function whenChunTriesToUsePremonitionOnKuanTi(): void
    {
        $this->premonition->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi
        );
    }

    private function whenChunUsesPremonitionOnKuanTi(): void
    {
        $this->whenChunTriesToUsePremonitionOnKuanTi();
        $this->premonition->execute();
    }

    private function thenChunShouldHaveMoralePoints(int $moralePoints, FunctionalTester $I): void
    {
        $I->assertEquals($moralePoints, $this->chun->getMoralPoint());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->premonition->cannotExecuteReason()
        );
    }
}
