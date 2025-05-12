<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Actions\Flirt;
use Mush\Action\Actions\GoBerserk;
use Mush\Action\Actions\Hit;
use Mush\Action\Actions\KillPlayer;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;
use Mush\User\Enum\RoleEnum;

/**
 * @internal
 */
final class GoBerserkCest extends AbstractFunctionalTest
{
    private ActionConfig $goBerserkActionConfig;
    private GoBerserk $goBerserk;

    private ActionConfig $moveActionConfig;
    private Move $move;

    private ActionConfig $kickOffActionConfig;
    private Hit $kickOff;
    private Weapon $bareHandMechanic;

    private Player $ian;

    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->goBerserkActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::GO_BERSERK]);
        $this->goBerserk = $I->grabService(GoBerserk::class);
        $this->moveActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::MOVE]);
        $this->move = $I->grabService(Move::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->kickOffActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HIT]);
        $this->kickOffActionConfig->setSuccessRate(100);
        $this->kickOff = $I->grabService(Hit::class);
        $this->bareHandMechanic = $I->grabEntityFromRepository(Weapon::class, ['name' => EquipmentMechanicEnum::WEAPON . '_' . ItemEnum::BARE_HANDS . '_' . GameConfigEnum::DEFAULT]);

        $this->givenKuanTiIsMush();
        $this->givenLaboratoryIsLinkedToFrontCorridor($I);
        $this->givenLaboratoryIsLinkedToMedlab($I);
        $this->givenDaedalusHasIcarusBay($I);
    }

    public function shouldNotBeExecutableOnNonRoomPlaces(FunctionalTester $I): void
    {
        $this->givenKuanTiIsOnPlanet();

        $this->whenKuanTiTriesToMutate();

        $this->thenActionShouldNotBeExecutableWithMessage(
            action: $this->goBerserk,
            message: ActionImpossibleCauseEnum::NOT_A_ROOM,
            I: $I,
        );
    }

    public function shouldNotBeVisibleIfPlayerAlreadyMutated(FunctionalTester $I): void
    {
        $this->whenKuanTiMutates();

        $this->whenKuanTiTriesToMutate();

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldPreventOtherPlayersToGoToOtherRooms(FunctionalTester $I): void
    {
        $this->whenChunMovesTo(RoomEnum::MEDLAB);

        $this->whenChunMovesTo(RoomEnum::LABORATORY);

        $this->whenKuanTiMutates();

        $this->thenChunShouldNotBeAbleToMoveTo(RoomEnum::FRONT_CORRIDOR, $I);
    }

    public function shouldNotPreventOtherPlayersToGoBackToTheirPreviousRoom(FunctionalTester $I): void
    {
        $this->whenChunMovesTo(RoomEnum::MEDLAB);

        $this->whenChunMovesTo(RoomEnum::LABORATORY);

        $this->whenKuanTiMutates();

        $this->thenChunShouldBeAbleToMoveTo(RoomEnum::MEDLAB, $I);
    }

    public function shouldNotBlockSneakPlayer(FunctionalTester $I): void
    {
        $this->whenChunMovesTo(RoomEnum::MEDLAB);

        $this->whenChunMovesTo(RoomEnum::LABORATORY);

        $this->givenChunIsSneak($I);

        $this->whenKuanTiMutates();

        $this->thenChunShouldBeAbleToMoveTo(RoomEnum::FRONT_CORRIDOR, $I);
    }

    public function shouldBeStillActiveWhenPlayerMoves(FunctionalTester $I): void
    {
        $this->whenKuanTiMutates();

        $this->whenKuanTiMovesTo(RoomEnum::MEDLAB);

        $this->thenKuanTiShouldHaveBerzerkStatus($I);
    }

    /*public function shouldPrintAPublicLog(FunctionalTester $I): void
    {
        $this->whenKuanTiMutates();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: '', // TODO: Message?
            actualRoomLogDto: new RoomLogDto(
                player: $this->kuanTi,
                log: ActionLogEnum::MUTATE_SUCCESS,
                visibility: VisibilityEnum::PUBLIC,
            ),
            I: $I,
        );
    }*/

    public function shouldNotPreventThemselvesFromGoingToOtherRooms(FunctionalTester $I): void
    {
        $this->whenKuanTiMovesTo(RoomEnum::MEDLAB);

        $this->whenKuanTiMovesTo(RoomEnum::LABORATORY);

        $this->whenKuanTiMutates();

        $this->thenKuanTiShouldBeAbleToMoveTo(RoomEnum::FRONT_CORRIDOR, $I);
    }

    public function shouldPreventThemselvesFromDoingGeneralAction(FunctionalTester $I): void
    {
        $this->whenKuanTiMutates();

        $flirt = $this->whenKuanTiTriesToFlirtChun($I);

        $this->thenActionShouldNotBeExecutableWithMessage(
            action: $flirt,
            message: ActionImpossibleCauseEnum::MUTATED,
            I: $I,
        );
    }

    public function shouldBeStoppedByGuardian(FunctionalTester $I): void
    {
        $this->whenKuanTiMovesTo(RoomEnum::MEDLAB);

        $this->whenKuanTiMovesTo(RoomEnum::LABORATORY);

        $this->whenKuanTiMutates();

        $this->givenChunGuardsTheRoom();

        $this->thenKuanTiShouldNotBeAbleToMoveTo(RoomEnum::FRONT_CORRIDOR, $I);
    }

    public function shouldNotBeStoppedByAnotherBerzerk(FunctionalTester $I): void
    {
        $this->whenKuanTiMovesTo(RoomEnum::MEDLAB);

        $this->whenChunMovesTo(RoomEnum::MEDLAB);

        $this->whenKuanTiMovesTo(RoomEnum::LABORATORY);

        $this->whenChunMovesTo(RoomEnum::LABORATORY);

        $this->givenIanInLab($I);

        $this->whenIanMutates();

        $this->thenKuanTiShouldNotBeAbleToMoveTo(RoomEnum::FRONT_CORRIDOR, $I);

        $this->whenKuanTiMutates();

        $this->thenKuanTiShouldBeAbleToMoveTo(RoomEnum::FRONT_CORRIDOR, $I);

        $this->thenChunShouldNotBeAbleToMoveTo(RoomEnum::FRONT_CORRIDOR, $I);
    }

    public function shouldIncreaseDamageByOnePoint(FunctionalTester $I): void
    {
        $this->whenKuanTiMutates();

        $initialHealthPoint = $this->chun->getVariableValueByName(PlayerVariableEnum::HEALTH_POINT);

        $this->givenHitDamagesFor(1);

        $this->whenKuanTiHitsChun();

        $this->thenChunShouldHaveHealthPointsOfAmount($initialHealthPoint - 2, $I);
    }

    public function shouldNotPreventThemselvesFromDoingAdminAction(FunctionalTester $I): void
    {
        $this->kuanTi->getUser()->setRoles([RoleEnum::SUPER_ADMIN]);

        $this->whenKuanTiMutates();

        $quarantine = $this->whenKuanTiTriesToQuarantineChun($I);

        $this->thenActionShouldBeExecutable($quarantine, $I);
    }

    private function givenKuanTiIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
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

    private function givenKuanTiIsOnPlanet(): void
    {
        $this->kuanTi->changePlace($this->daedalus->getPlanetPlace());
    }

    private function givenChunGuardsTheRoom(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::GUARDIAN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenChunIsSneak(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::SNEAK, $I, $this->chun);
    }

    private function givenIanInLab(FunctionalTester $I): void
    {
        $this->ian = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::IAN);

        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->ian,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenHitDamagesFor(int $damage): void
    {
        $this->bareHandMechanic->setDamageSpread([$damage, $damage]);
    }

    private function whenKuanTiTriesToMutate(): void
    {
        $this->goBerserk->loadParameters(
            actionConfig: $this->goBerserkActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
        );
    }

    private function whenKuanTiMutates(): void
    {
        $this->whenKuanTiTriesToMutate();
        $this->goBerserk->execute();
    }

    private function whenIanMutates(): void
    {
        $this->goBerserk->loadParameters(
            actionConfig: $this->goBerserkActionConfig,
            actionProvider: $this->ian,
            player: $this->ian,
        );
        $this->goBerserk->execute();
    }

    private function whenKuanTiMovesTo(string $room): void
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

    private function whenKuanTiTriesToFlirtChun(FunctionalTester $I): Flirt
    {
        $flirtActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::FLIRT]);
        $flirt = $I->grabService(Flirt::class);

        $flirt->loadParameters(
            actionConfig: $flirtActionConfig,
            actionProvider: $this->chun,
            player: $this->kuanTi,
            target: $this->chun,
        );

        return $flirt;
    }

    private function whenKuanTiTriesToQuarantineChun(FunctionalTester $I): KillPlayer
    {
        $quarantineActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::KILL_PLAYER]);
        $quarantine = $I->grabService(KillPlayer::class);

        $quarantine->loadParameters(
            actionConfig: $quarantineActionConfig,
            actionProvider: $this->chun,
            player: $this->kuanTi,
            target: $this->chun,
        );

        return $quarantine;
    }

    private function whenKuanTiHitsChun(): void
    {
        $this->kickOff->loadParameters(
            actionConfig: $this->kickOffActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->chun
        );
        $this->kickOff->execute();
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: false,
            actual: $this->goBerserk->isVisible(),
        );
    }

    private function thenActionShouldNotBeExecutableWithMessage(AbstractAction $action, string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $action->cannotExecuteReason(),
        );
    }

    private function thenActionShouldBeExecutable(AbstractAction $action, FunctionalTester $I): void
    {
        $I->assertNull(
            actual: $action->cannotExecuteReason(),
            message: "Action should be executable, cannot because: {$action->cannotExecuteReason()}"
        );
    }

    private function thenChunShouldNotBeAbleToMoveTo(string $room, FunctionalTester $I): void
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
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::CANNOT_GO_TO_THIS_ROOM_BECAUSE_GUARDIAN,
            actual: $this->move->cannotExecuteReason(),
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

    private function thenKuanTiShouldHaveBerzerkStatus(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: true,
            actual: $this->kuanTi->hasStatus(PlayerStatusEnum::BERZERK),
        );
    }

    private function thenChunShouldBeAbleToMoveTo(string $room, FunctionalTester $I): void
    {
        $door = $this->chun
            ->getPlace()
            ->getDoors()
            ->filter(fn (Door $door) => $door->getOtherRoom($this->chun->getPlace())->getName() === $room)
            ->first();

        $this->move->loadParameters(
            actionConfig: $this->moveActionConfig,
            actionProvider: $door,
            player: $this->chun,
            target: $door,
        );
        $I->assertNull($this->move->cannotExecuteReason());
    }

    private function thenChunShouldHaveHealthPointsOfAmount(int $expectedHealthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedHealthPoints, $this->chun->getVariableValueByName(PlayerVariableEnum::HEALTH_POINT));
    }

    private function createDoorFromTo(Place $from, Place $to, FunctionalTester $I): void
    {
        $doorConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']);
        $door = Door::createFromRooms($from, $to)->setEquipment($doorConfig);
        $I->haveInRepository($door);
    }
}
