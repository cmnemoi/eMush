<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Sabotage;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Triumph\Enum\TriumphEnum;

/**
 * @internal
 */
final class SabotageCest extends AbstractFunctionalTest
{
    private ActionConfig $sabotageActionConfig;
    private Sabotage $sabotageAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private GameEquipment $pasiphae;
    private StatusServiceInterface $statusService;
    private GameItem $blaster;
    private GameEquipment $camera;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);

        $this->sabotageActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => 'sabotage_percent_12']);
        $this->sabotageAction = $I->grabService(Sabotage::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->pasiphae = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PASIPHAE,
            equipmentHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::PASIPHAE),
            reasons: [],
            time: new \DateTime()
        );
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenPlayerHasABlaster();
    }

    public function testSabotageIsNotExecutableIfPatrolShipNotInARoom(FunctionalTester $I): void
    {
        // given player is in pasiphae room
        $this->player->changePlace($this->pasiphae->getPlace());

        // when player try to sabotage pasiphae
        $this->sabotageAction->loadParameters(
            actionConfig: $this->sabotageActionConfig,
            actionProvider: $this->pasiphae,
            player: $this->player,
            target: $this->pasiphae
        );

        // then sabotage is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::NOT_A_ROOM, $this->sabotageAction->cannotExecuteReason());
    }

    public function saboteurShouldHaveDoubledSuccessRate(FunctionalTester $I): void
    {
        $this->givenPlayerHasSaboteurSkill($I);

        $this->givenActionSuccessRateIs(12);

        $this->whenPlayerTriesToSabotagePasiphae();

        $this->thenActionSuccessRateShouldBe(24, $I);
    }

    public function shouldNotBeExecutableOnReinforcedEquipment(FunctionalTester $I): void
    {
        $this->givenPlayerHasABlaster();

        $this->givenPlayerIsMush();

        $this->givenBlasterIsReinforced();

        $this->whenPlayerTriesToSabotageBlaster();

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::SABOTAGE_REINFORCED,
            I: $I,
        );
    }

    public function cameraShouldNotRevealTheirOwnSabotage(FunctionalTester $I): void
    {
        $this->givenACameraInPlayerRoom();

        $this->givenPlayerIsMush();

        $this->givenActionSuccessRateIs(100);

        $this->player2->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE));

        $this->whenPlayerSabotagesCamera();

        $log = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'log' => ActionLogEnum::SABOTAGE_SUCCESS,
            ]
        );
        $I->assertEquals(VisibilityEnum::SECRET, $log->getVisibility());
    }

    public function shouldGiveSabotageCustomTriumphOnSuccess(FunctionalTester $I): void
    {
        $this->givenCustomSabotageConfigRewardsWithTriumph(7);

        $this->givenACameraInPlayerRoom();

        $this->givenPlayerIsMush();

        $this->givenActionSuccessRateIs(100);

        $this->whenPlayerSabotagesCamera();

        $this->thenPlayerShouldHaveTriumph(7, $I);
    }

    public function shouldNotGiveSabotageCustomTriumphOnFailure(FunctionalTester $I): void
    {
        $this->givenCustomSabotageConfigRewardsWithTriumph(7);

        $this->givenACameraInPlayerRoom();

        $this->givenPlayerIsMush();

        $this->givenActionSuccessRateIs(0);

        $this->whenPlayerSabotagesCamera();

        $this->thenPlayerShouldHaveTriumph(0, $I);
    }

    private function givenPlayerHasSaboteurSkill(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::SABOTEUR, $I);
    }

    private function givenActionSuccessRateIs(int $successRate): void
    {
        $this->sabotageActionConfig->setSuccessRate($successRate);
    }

    private function givenACameraInPlayerRoom(): void
    {
        $this->camera = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::CAMERA_EQUIPMENT,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenCustomSabotageConfigRewardsWithTriumph(int $quantity): void
    {
        $this->daedalus->getGameConfig()->getTriumphConfig()->getByNameOrThrow(TriumphEnum::CM_SABOTAGE)->setQuantity($quantity);
    }

    private function whenPlayerTriesToSabotagePasiphae(): void
    {
        $this->sabotageAction->loadParameters(
            actionConfig: $this->sabotageActionConfig,
            actionProvider: $this->pasiphae,
            player: $this->player,
            target: $this->pasiphae
        );
    }

    private function whenPlayerSabotagesCamera(): void
    {
        $this->sabotageAction->loadParameters(
            actionConfig: $this->sabotageActionConfig,
            actionProvider: $this->camera,
            player: $this->player,
            target: $this->camera
        );
        $this->sabotageAction->execute();
    }

    private function thenActionSuccessRateShouldBe(int $expectedSuccessRate, FunctionalTester $I): void
    {
        $I->assertEquals($expectedSuccessRate, $this->sabotageAction->getSuccessRate());
    }

    private function givenPlayerHasABlaster(): void
    {
        $this->blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenBlasterIsReinforced(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::REINFORCED,
            holder: $this->blaster,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenPlayerTriesToSabotageBlaster(): void
    {
        $this->sabotageAction->loadParameters(
            actionConfig: $this->sabotageActionConfig,
            actionProvider: $this->blaster,
            player: $this->player,
            target: $this->blaster,
        );
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->sabotageAction->cannotExecuteReason());
    }

    private function thenPlayerShouldHaveTriumph(int $quantity, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $this->player->getTriumph());
    }
}
