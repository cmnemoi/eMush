<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Search;
use Mush\Action\Actions\Torture;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class TortureCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Torture $torture;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TORTURE->value]);
        $this->torture = $I->grabService(Torture::class);

        $this->addSkillToPlayer(SkillEnum::TORTURER, $I);
    }

    public function shouldRemoveOneHealthPointFromTarget(FunctionalTester $I): void
    {
        $this->givenKuanTiHasHealthPoints(10);

        $this->whenChunTorturesKuanTi();

        $this->thenKuanTiShouldHaveHealthPoints(9, $I);
    }

    public function shouldRevealAsMuchActionsAsMissingTargetHealthPoints(FunctionalTester $I): void
    {
        $this->givenKuanTiExecutedSearchActionXTimes(5, $I);

        $this->givenKuanTiHasHealthPoints(13);

        $this->whenChunTorturesKuanTi();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "Vous vous en prenez violemment à **Kuan Ti**. Vous attrapez un annuaire qui trainait par là et vous lui mettez de grands coups avec... C'est sanguinolent, mais **Kuan Ti** finit par parler... Ses dernières actions sont **Fouiller** et **Fouiller**.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::TORTURER_ACTIONS,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    public function logShouldAdaptWhenRevealingOneAction(FunctionalTester $I): void
    {
        $this->givenKuanTiExecutedSearchActionXTimes(1, $I);

        $this->givenKuanTiHasHealthPoints(14);

        $this->whenChunTorturesKuanTi();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "Vous vous en prenez violemment à **Kuan Ti**. Vous attrapez un annuaire qui trainait par là et vous lui mettez de grands coups avec... C'est sanguinolent, mais **Kuan Ti** finit par parler... Sa dernière action est **Fouiller**.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::TORTURER_ACTIONS,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    public function logShouldAdaptWhenThereIsNoActionToReveal(FunctionalTester $I): void
    {
        $this->givenKuanTiHasHealthPoints(14);

        $this->whenChunTorturesKuanTi();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Vous vous en prenez violemment à **Kuan Ti**. Sa tête part dans tout les sens... Mais apparemment rien à en tirer...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::TORTURER_ACTIONS,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }

    private function givenKuanTiHasHealthPoints(int $healthPoints): void
    {
        $this->kuanTi->setHealthPoint($healthPoints);
    }

    private function givenKuanTiExecutedSearchActionXTimes(int $number, FunctionalTester $I): void
    {
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::SEARCH->value]);
        $actionConfig->setName(\sprintf('%s_percent_100', ActionEnum::SEARCH->value));

        $search = $I->grabService(Search::class);

        for ($i = 0; $i < $number; ++$i) {
            $search->loadParameters(
                actionConfig: $actionConfig,
                actionProvider: $this->kuanTi,
                player: $this->kuanTi,
                target: null
            );
            $search->execute();
        }
    }

    private function whenChunTorturesKuanTi(): void
    {
        $this->torture->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->torture->execute();
    }

    private function thenKuanTiShouldHaveHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($healthPoints, $this->kuanTi->getHealthPoint());
    }

    private function thenIShouldSeeAPrivateLogForTorturerWithActions(string $actions, FunctionalTester $I): void
    {
        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "Vous vous en prenez violemment à **Kuan Ti**. Vous attrapez un annuaire qui trainait par là et vous lui mettez de grands coups avec... C'est sanguinolent, mais **Kuan Ti** finit par parler... Ses dernières actions sont {$actions}.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::TORTURER_ACTIONS,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I
        );
    }
}
