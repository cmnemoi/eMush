<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Guard;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class GuardCest extends AbstractFunctionalTest
{
    private ActionConfig $guardActionConfig;
    private Guard $guard;

    private ActionConfig $moveActionConfig;
    private Move $move;

    private ChooseSkillUseCase $chooseSkillUseCase;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->guardActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::GUARD]);
        $this->guard = $I->grabService(Guard::class);
        $this->moveActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::MOVE]);
        $this->move = $I->grabService(Move::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

        $this->givenLaboratoryIsLinkedToFrontCorridor($I);
        $this->givenLaboratoryIsLinkedToMedlab($I);
        $this->givenDaedalusHasIcarusBay($I);
    }

    public function shouldNotBeExecutableOnNonRoomPlaces(FunctionalTester $I): void
    {
        $this->givenChunIsOnPlanet();

        $this->whenChunTriesToGuardTheRoom();

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::NOT_A_ROOM,
            I: $I,
        );
    }

    public function shouldNotBeVisibleIfPlayerAlreadyGuardsTheRoom(FunctionalTester $I): void
    {
        $this->givenChunGuardsTheRoom();

        $this->whenChunTriesToGuardTheRoom();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldPreventOtherPlayersToGoToOtherRooms(FunctionalTester $I): void
    {
        $this->givenKuanTiMovesTo(RoomEnum::MEDLAB);

        $this->givenKuanTiMovesTo(RoomEnum::LABORATORY);

        $this->whenChunGuardsTheRoom();

        $this->thenKuanTiShouldNotBeAbleToMoveTo(RoomEnum::FRONT_CORRIDOR, $I);
    }

    public function shouldNotPreventOtherPlayersToGoBackToTheirPreviousRoom(FunctionalTester $I): void
    {
        $this->givenKuanTiMovesTo(RoomEnum::MEDLAB);

        $this->givenKuanTiMovesTo(RoomEnum::LABORATORY);

        $this->whenChunGuardsTheRoom();

        $this->thenKuanTiShouldBeAbleToMoveTo(RoomEnum::MEDLAB, $I);
    }

    public function shouldNotBlockSneakPlayer(FunctionalTester $I): void
    {
        $this->givenKuanTiMovesTo(RoomEnum::MEDLAB);

        $this->givenKuanTiMovesTo(RoomEnum::LABORATORY);

        $this->givenKuanTiIsSneak($I);

        $this->whenChunGuardsTheRoom();

        $this->thenKuanTiShouldBeAbleToMoveTo(RoomEnum::FRONT_CORRIDOR, $I);
    }

    public function shouldNotBeActiveAnymoreWhenPlayerMoves(FunctionalTester $I): void
    {
        $this->givenChunGuardsTheRoom();

        $this->whenChunMovesTo(RoomEnum::MEDLAB);

        $this->thenChunDoesNotHaveGuardianStatus($I);
    }

    public function shouldPrintAPublicLog(FunctionalTester $I): void
    {
        $this->whenChunGuardsTheRoom();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: '**Chun** prend un air ennuyeux et se met à garder la pièce...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: ActionLogEnum::GUARD_SUCCESS,
                visibility: VisibilityEnum::PUBLIC,
            ),
            I: $I,
        );
    }

    private function givenLaboratoryIsLinkedToFrontCorridor(FunctionalTester $I): void
    {
        $laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);

        $this->createDoorFromTo($laboratory, $frontCorridor, $I);
    }

    private function givenLaboratoryIsLinkedToMedlab(FunctionalTester $I): void
    {
        $laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $medlab = $this->createExtraPlace(RoomEnum::MEDLAB, $I, $this->daedalus);

        $this->createDoorFromTo($laboratory, $medlab, $I);
    }

    private function givenDaedalusHasIcarusBay(FunctionalTester $I): void
    {
        $this->createExtraPlace(RoomEnum::ICARUS_BAY, $I, $this->daedalus);
    }

    private function givenChunIsOnPlanet(): void
    {
        $this->chun->changePlace($this->daedalus->getPlanetPlace());
    }

    private function givenKuanTiMovesTo(string $room): void
    {
        $door = $this->kuanTi->getPlace()
            ->getDoors()
            ->filter(fn (Door $door) => $door->getOtherRoom($this->kuanTi->getPlace())->getName() === $room)
            ->first();

        $this->move->loadParameters(
            actionConfig: $this->moveActionConfig,
            actionProvider: $door,
            player: $this->kuanTi,
            target: $door,
        );
        $this->move->execute();
    }

    private function givenChunGuardsTheRoom(): void
    {
        $this->whenChunGuardsTheRoom();
    }

    private function givenKuanTiIsSneak(FunctionalTester $I): void
    {
        $this->kuanTi->getCharacterConfig()->addSkillConfig(
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::SNEAK])
        );
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::SNEAK, $this->kuanTi));
    }

    private function whenChunTriesToGuardTheRoom(): void
    {
        $this->guard->loadParameters(
            actionConfig: $this->guardActionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
        );
    }

    private function whenChunGuardsTheRoom(): void
    {
        $this->whenChunTriesToGuardTheRoom();
        $this->guard->execute();
    }

    private function whenChunMovesTo(string $room): void
    {
        $door = $this->chun->getPlace()
            ->getDoors()
            ->filter(fn (Door $door) => $door->getOtherRoom($this->chun->getPlace())->getName() === $room)
            ->first();

        $this->move->loadParameters(
            actionConfig: $this->moveActionConfig,
            actionProvider: $door,
            player: $this->chun,
            target: $door,
        );
        $this->move->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: false,
            actual: $this->guard->isVisible(),
        );
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->guard->cannotExecuteReason(),
        );
    }

    private function thenKuanTiShouldNotBeAbleToMoveTo(string $room, FunctionalTester $I): void
    {
        $door = $this->kuanTi->getPlace()
            ->getDoors()
            ->filter(fn (Door $door) => $door->getOtherRoom($this->kuanTi->getPlace())->getName() === $room)
            ->first();

        $this->move->loadParameters(
            actionConfig: $this->moveActionConfig,
            actionProvider: $door,
            player: $this->kuanTi,
            target: $door,
        );
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::CANNOT_GO_TO_THIS_ROOM_BECAUSE_GUARDIAN,
            actual: $this->move->cannotExecuteReason(),
        );
    }

    private function thenKuanTiShouldBeAbleToMoveTo(string $room, FunctionalTester $I): void
    {
        $door = $this->kuanTi->getPlace()
            ->getDoors()
            ->filter(fn (Door $door) => $door->getOtherRoom($this->kuanTi->getPlace())->getName() === $room)
            ->first();

        $this->move->loadParameters(
            actionConfig: $this->moveActionConfig,
            actionProvider: $door,
            player: $this->kuanTi,
            target: $door,
        );
        $I->assertNull($this->move->cannotExecuteReason());
    }

    private function thenChunDoesNotHaveGuardianStatus(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: false,
            actual: $this->chun->hasStatus(PlayerStatusEnum::GUARDIAN),
        );
    }

    private function createDoorFromTo(Place $from, Place $to, FunctionalTester $I): void
    {
        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']);
        $door = Door::createFromRooms($from, $to)->setEquipment($doorConfig);
        $I->haveInRepository($door);
    }
}
