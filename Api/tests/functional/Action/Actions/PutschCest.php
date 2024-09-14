<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Putsch;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
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

    private GameEquipmentServiceInterface $gameEquipmentService;
    private GameEquipment $neronCore;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PUTSCH]);
        $this->putsch = $I->grabService(Putsch::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->givenChunHasActionPoints(12);
        $this->givenANeronCoreInChunRoom();
    }

    public function shouldPutPlayerInFirstPlaceForCommanderTitle(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLITICIAN, $I, $this->chun);

        $this->whenChunPutsches();

        $this->thenChunShouldBeInFirstPlaceForCommanderTitle($I);
    }

    public function shouldPrintAPublicLog(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLITICIAN, $I, $this->chun);

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
        $this->addSkillToPlayer(SkillEnum::POLITICIAN, $I, $this->chun);

        $this->givenChunPutsches();

        $this->whenChunPutsches();

        $this->thenActionIsNotExecutableWithMessage(
            message: ActionImpossibleCauseEnum::UNIQUE_ACTION,
            I: $I,
        );
    }

    public function shouldNotBeVisibleIfPlayerIsNotPolitician(FunctionalTester $I): void
    {
        $this->whenChunPutsches();

        $this->thenActionIsNotVisible($I);
    }

    private function givenChunHasActionPoints(int $actionPoints): void
    {
        $this->chun->setActionPoint($actionPoints);
    }

    private function givenANeronCoreInChunRoom(): void
    {
        $this->neronCore = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::NERON_CORE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
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
            actionProvider: $this->neronCore,
            player: $this->chun,
            target: $this->neronCore,
        );
        $this->putsch->execute();
    }

    private function thenChunShouldBeInFirstPlaceForCommanderTitle(FunctionalTester $I): void
    {
        $commanderTitlePriorities = $this->daedalus
            ->getTitlePriorityByNameOrThrow(TitleEnum::COMMANDER)
            ->getPriority();

        $I->assertEquals($this->chun->getName(), $commanderTitlePriorities[0]);
    }

    private function thenActionIsNotExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->putsch->cannotExecuteReason());
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->putsch->isVisible());
    }
}
