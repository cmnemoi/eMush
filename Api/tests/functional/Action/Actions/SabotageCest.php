<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Sabotage;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SabotageCest extends AbstractFunctionalTest
{
    private ActionConfig $sabotageActionConfig;
    private Sabotage $sabotageAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private GameEquipment $pasiphae;

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

    private function givenPlayerHasSaboteurSkill(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::SABOTEUR, $I);
    }

    private function givenActionSuccessRateIs(int $successRate): void
    {
        $this->sabotageActionConfig->setSuccessRate($successRate);
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

    private function thenActionSuccessRateShouldBe(int $expectedSuccessRate, FunctionalTester $I): void
    {
        $I->assertEquals($expectedSuccessRate, $this->sabotageAction->getSuccessRate());
    }
}
