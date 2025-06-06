<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\ComputeEden;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class ComputeEdenCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ComputeEden $computeEden;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameEquipment $calculator;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::COMPUTE_EDEN->toString()]);
        $this->computeEden = $I->grabService(ComputeEden::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenCalculatorInRoom();
    }

    public function shouldNotBeVisibleIfPlayerIsNotFocusedOnCalculator(FunctionalTester $I): void
    {
        $this->whenPlayerTriesToComputeEden();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldNotBeVisibleIfThereAreNoStarmapFragmentsInTheRoom(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCalculator();

        $this->givenStarmapFragmentsInRoom(number: 0);

        $this->whenPlayerTriesToComputeEden();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldNotBeVisibleIfEdenIsAlreadyComputed(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCalculator();

        $this->givenStarmapFragmentsInRoom(number: 3);

        $this->givenEdenCoordinatesAreComputed();

        $this->whenPlayerTriesToComputeEden();

        $this->thenActionIsNotVisible($I);
    }

    public function shouldNotBeExecutableIfThereAreLessThanThreeStarmapFragmentsInTheRoom(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCalculator();

        $this->givenStarmapFragmentsInRoom(number: 2);

        $this->whenPlayerTriesToComputeEden();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::NOT_ENOUGH_MAP_FRAGMENTS, $I);
    }

    public function shouldNotBeExecutableIfPlayerIsDirty(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCalculator();

        $this->givenStarmapFragmentsInRoom(number: 3);

        $this->givenPlayerIsDirty();

        $this->whenPlayerTriesToComputeEden();

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DIRTY_RESTRICTION, $I);
    }

    public function shouldCreateEdenComputedStatusOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCalculator();

        $this->givenStarmapFragmentsInRoom(number: 3);

        $this->givenActionSuccessRateIs(100);

        $this->whenPlayerTriesToComputeEden();

        $this->thenDaedalusShouldHaveEdenComputedStatus($I);
    }

    public function itExpertShouldUseOneITPoint(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCalculator();

        $this->givenStarmapFragmentsInRoom(number: 3);

        $this->givenPlayerIsAnITExpert($I);

        $this->givenPlayerHasFourITPoints($I);

        $this->whenPlayerTriesToComputeEden();

        $this->thenPlayerShouldHaveITPoints(3, $I);
    }

    public function shouldCreatePublicLogOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCalculator();

        $this->givenStarmapFragmentsInRoom(number: 3);

        $this->givenActionSuccessRateIs(100);

        $this->whenPlayerTriesToComputeEden();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "**Chun** se prend la tête mais rien n'y fait, aucun moyen de trianguler les données... Soudain une secousse fait tomber une carte mère sur le sommet de son crâne et son regard s'illumine ! Il fallait penser autrement ! **Chun** découvre les coordonnées du système des Edénistes.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: ActionLogEnum::COMPUTE_EDEN_SUCCESS,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldCreatePrivateLogOnFailure(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCalculator();

        $this->givenStarmapFragmentsInRoom(number: 3);

        $this->givenActionSuccessRateIs(0);

        $this->whenPlayerTriesToComputeEden();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "Vous vous prenez la tête mais rien n'y fait, aucun moyen de trianguler les données...",
            actualRoomLogDto: new RoomLogDto(
                player: $this->player,
                log: ActionLogEnum::COMPUTE_EDEN_FAIL,
                visibility: VisibilityEnum::PRIVATE,
            ),
            I: $I,
        );
    }

    public function shouldNotCreateEdenComputedStatusOnFailure(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCalculator();

        $this->givenStarmapFragmentsInRoom(number: 3);

        $this->givenActionSuccessRateIs(0);

        $this->whenPlayerTriesToComputeEden();

        $this->thenDaedalusShouldNotHaveEdenComputedStatus($I);
    }

    public function shouldProvideTriumphOnSuccess(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCalculator();

        $this->givenStarmapFragmentsInRoom(number: 3);

        $this->givenActionSuccessRateIs(100);

        $this->givenZeroTriumph();

        $this->whenPlayerTriesToComputeEden();

        $this->thenPlayerShouldHaveTriumph(4, $I);
    }

    public function shouldNotProvideTriumphOnFailure(FunctionalTester $I): void
    {
        $this->givenPlayerIsFocusedOnCalculator();

        $this->givenStarmapFragmentsInRoom(number: 3);

        $this->givenActionSuccessRateIs(0);

        $this->givenZeroTriumph();

        $this->whenPlayerTriesToComputeEden();

        $this->thenPlayerShouldHaveTriumph(0, $I);
    }

    private function givenCalculatorInRoom(): void
    {
        $this->calculator = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::CALCULATOR,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsFocusedOnCalculator(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->calculator
        );
    }

    private function givenStarmapFragmentsInRoom(int $number): void
    {
        $this->gameEquipmentService->createGameEquipmentsFromName(
            equipmentName: ItemEnum::STARMAP_FRAGMENT,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
            quantity: $number
        );
    }

    private function givenActionSuccessRateIs(int $successRate): void
    {
        $this->actionConfig->setSuccessRate($successRate);
    }

    private function givenEdenCoordinatesAreComputed(): void
    {
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::EDEN_COMPUTED,
            holder: $this->player->getDaedalus(),
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsDirty(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->player,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerIsAnITExpert(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::IT_EXPERT, $I);
    }

    private function givenPlayerHasFourITPoints(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: 4,
            actual: $this->player->getSkillByNameOrThrow(SkillEnum::IT_EXPERT)->getSkillPoints(),
        );
    }

    private function givenZeroTriumph(): void
    {
        /** @var Player $player */
        foreach ($this->daedalus->getPlayers() as $player) {
            $player->setTriumph(0);
        }
    }

    private function whenPlayerTriesToComputeEden(): void
    {
        $this->computeEden->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->calculator,
            player: $this->player,
            target: $this->calculator
        );
        $this->computeEden->execute();
    }

    private function thenActionIsNotVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->computeEden->isVisible());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->computeEden->cannotExecuteReason());
    }

    private function thenDaedalusShouldHaveEdenComputedStatus(FunctionalTester $I): void
    {
        $I->assertTrue($this->daedalus->hasStatus(DaedalusStatusEnum::EDEN_COMPUTED));
    }

    private function thenDaedalusShouldNotHaveEdenComputedStatus(FunctionalTester $I): void
    {
        $I->assertFalse($this->daedalus->hasStatus(DaedalusStatusEnum::EDEN_COMPUTED));
    }

    private function thenPlayerShouldHaveITPoints(int $itPoints, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $itPoints,
            actual: $this->player->getSkillByNameOrThrow(SkillEnum::IT_EXPERT)->getSkillPoints(),
        );
    }

    private function thenPlayerShouldHaveTriumph(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->player->getTriumph());
        $I->assertEquals(0, $this->player2->getTriumph());
    }
}
