<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Ceasefire;
use Mush\Action\Actions\Hit;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class CeasefireCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Ceasefire $ceasefire;
    private ChooseSkillUseCase $chooseSkillUseCase;
    private Hit $hit;
    private Move $move;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CEASEFIRE]);
        $this->ceasefire = $I->grabService(Ceasefire::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->hit = $I->grabService(Hit::class);
        $this->move = $I->grabService(Move::class);

        $this->givenChunIsADiplomat($I);
        $this->givenKuanTiIsADiplomat($I);
    }

    public function shouldNotBeVisibleIfPlayerNotInARoom(FunctionalTester $I): void
    {
        $this->givenChunIsInSpace();

        $this->whenChunTriesToCeasefire();

        $this->thenCeasefireActionIsNotVisible(I: $I);
    }

    public function shouldNotBeExecutableIfThereIsAlreadyACeasefireInRoom(FunctionalTester $I): void
    {
        $this->givenChunCeasefires();

        $this->whenKuanTiTriesToCeasefire();

        $this->thenCeasefireActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::ALREADY_A_CEASEFIRE_IN_ROOM,
            I: $I
        );
    }

    public function shouldPreventAggressiveActionsInRoom(FunctionalTester $I): void
    {
        $this->givenChunCeasefires();

        $this->whenChunWantsToHitKuanTi($I);

        $this->thenHitActionShouldNotBeExecutableWithMessage(message: ActionImpossibleCauseEnum::CEASEFIRE, I: $I);
    }

    public function shouldPrintAPublicLog(FunctionalTester $I): void
    {
        $this->whenChunCeasefires();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: '**Chun** impose un cessez-le-feu dans la pièce. Quelle autorité !',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::CEASEFIRE_SUCCESS,
                visibility: VisibilityEnum::PUBLIC,
            ),
            I: $I,
        );
    }

    public function shouldBeExecutableOncePerPlayer(FunctionalTester $I): void
    {
        $this->givenChunCeasefires();

        $this->givenChunGoesToFrontCorridor($I);

        $this->whenChunTriesToCeasefire();

        $this->thenCeasefireActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::UNIQUE_ACTION,
            I: $I,
        );
    }

    public function shouldDisappearWhenDiplomatMoves(FunctionalTester $I): void
    {
        $this->givenChunCeasefires();

        $this->whenChunGoesToFrontCorridor($I);

        $this->thenRoomShouldNotBeUnderCeasefire($I);
    }

    public function shouldPrintAPublicLogWhenDiplomatMoves(FunctionalTester $I): void
    {
        $this->givenChunCeasefires();

        $this->whenChunGoesToFrontCorridor($I);

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: '**Chun** a quitté la pièce. Fin du cessez-le-feu.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: StatusEventLogEnum::CEASEFIRE_END,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    private function givenChunIsInSpace(): void
    {
        $this->player->changePlace($this->daedalus->getSpace());
    }

    private function givenChunIsADiplomat(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::DIPLOMAT]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::DIPLOMAT, $this->player));
    }

    private function givenKuanTiIsADiplomat(FunctionalTester $I): void
    {
        $this->kuanTi->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::DIPLOMAT]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::DIPLOMAT, $this->kuanTi));
    }

    private function givenChunCeasefires(): void
    {
        $this->whenChunCeasefires();
    }

    private function givenChunGoesToFrontCorridor(FunctionalTester $I): void
    {
        $this->chun->changePlace($this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus));
    }

    private function whenChunTriesToCeasefire(): void
    {
        $this->ceasefire->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: null,
        );
    }

    private function whenKuanTiTriesToCeasefire(): void
    {
        $this->ceasefire->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: null,
        );
    }

    private function whenChunCeasefires(): void
    {
        $this->whenChunTriesToCeasefire();
        $this->ceasefire->execute();
    }

    private function whenChunWantsToHitKuanTi(FunctionalTester $I): void
    {
        $this->hit->loadParameters(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HIT]),
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
    }

    private function whenChunGoesToFrontCorridor(FunctionalTester $I): void
    {
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        $icarusBay = $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);
        $door = Door::createFromRooms($frontCorridor, $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
        $door->setEquipment($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']));
        $I->haveInRepository($door);

        $this->move->loadParameters(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::MOVE]),
            actionProvider: $door,
            player: $this->chun,
            target: $door,
        );
        $this->move->execute();
    }

    private function thenCeasefireActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->ceasefire->isVisible());
    }

    private function thenHitActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->hit->cannotExecuteReason(),
        );
    }

    private function thenCeasefireActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->ceasefire->cannotExecuteReason(),
        );
    }

    private function thenRoomShouldNotBeUnderCeasefire(FunctionalTester $I): void
    {
        $I->assertFalse($this->kuanTi->getPlace()->hasStatus(PlaceStatusEnum::CEASEFIRE->toString()));
    }
}
