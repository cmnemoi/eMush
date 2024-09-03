<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Putsch;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Daedalus\Entity\TitlePriority;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class PutschCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Putsch $putsch;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PUTSCH]);
        $this->putsch = $I->grabService(Putsch::class);

        $this->addSkillToPlayer(SkillEnum::POLITICIAN, $I, $this->chun);
        $this->chun->setActionPoint(12);
    }

    public function shouldPutPlayerInFirstPlaceForCommanderTitle(FunctionalTester $I): void
    {
        $this->whenChunPutsches();

        $this->thenChunShouldBeInFirstPlaceForCommanderTitle($I);
    }

    public function shouldPrintAPublicLog(FunctionalTester $I): void
    {
        $this->whenChunPutsches();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "**Chun** entre en grande discussion avec NERON. Qui sait ce qu'ils peuvent bien se dire…",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::PUTSCH_SUCCESS,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldBeExecutableOncePerGame(FunctionalTester $I): void
    {
        $this->givenChunPutsches();

        $this->whenChunPutsches();

        $this->thenActionIsNotExecutableWithMessage(
            message: ActionImpossibleCauseEnum::UNIQUE_ACTION,
            I: $I,
        );
    }

    private function givenChunPutsches(): void
    {
        $this->whenChunPutsches();
    }

    private function whenChunPutsches(): void
    {
        $this->putsch->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
        );
        $this->putsch->execute();
    }

    private function thenChunShouldBeInFirstPlaceForCommanderTitle(FunctionalTester $I): void
    {
        $commanderTitlePriorities = $this->daedalus
            ->getTitlePriorities()
            ->filter(static fn (TitlePriority $titlePriority) => $titlePriority->getName() === TitleEnum::COMMANDER)
            ->first()
            ->getPriority();

        $I->assertEquals($this->chun->getName(), $commanderTitlePriorities[0]);
    }

    private function thenActionIsNotExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->putsch->cannotExecuteReason());
    }
}
