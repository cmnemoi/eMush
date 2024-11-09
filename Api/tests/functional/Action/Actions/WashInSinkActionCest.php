<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\WashInSink;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class WashInSinkActionCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private WashInSink $washInSinkAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameEquipment $kitchen;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::WASH_IN_SINK]);
        $this->washInSinkAction = $I->grabService(WashInSink::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->kitchen = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::KITCHEN,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    public function testHumanWashInSink(FunctionalTester $I)
    {
        // given Chun has 6 action points
        $this->chun->setActionPoint(6);

        // given Chun is dirty
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DIRTY,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given I have a kitchen in Chun' room
        $kitchen = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::KITCHEN,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // when Chun washes in the sink
        $this->washInSinkAction->loadParameters($this->actionConfig, $kitchen, $this->chun, $kitchen);
        $this->washInSinkAction->execute();

        // then Chun has 3 action points
        $I->assertEquals(3, $this->chun->getActionPoint());

        // then Chun has no dirty status
        $I->assertFalse($this->chun->hasStatus(PlayerStatusEnum::DIRTY));

        // then I should see a private room log telling that Chun washed in the sink
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->chun->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->chun->getPlayerInfo(),
            'log' => ActionLogEnum::WASH_IN_SINK_HUMAN,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);

        // then Wash in the sink action is not available anymore
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DAILY_LIMIT,
            actual: $this->washInSinkAction->cannotExecuteReason()
        );
    }

    public function testWashInSinkIsAvailableOncePerDayForAllPlayers(FunctionalTester $I): void
    {
        // given I have a kitchen in players' room
        $kitchen = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::KITCHEN,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );

        // when Chun takes a shower
        $this->washInSinkAction->loadParameters($this->actionConfig, $kitchen, $this->chun, $kitchen);
        $this->washInSinkAction->execute();

        // then Chun cannot take a shower again
        $this->washInSinkAction->loadParameters($this->actionConfig, $kitchen, $this->chun, $kitchen);
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DAILY_LIMIT,
            actual: $this->washInSinkAction->cannotExecuteReason()
        );

        // then Kuan Ti cannot take a shower
        $this->washInSinkAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $kitchen,
            player: $this->kuanTi,
            target: $kitchen
        );
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DAILY_LIMIT,
            actual: $this->washInSinkAction->cannotExecuteReason()
        );
    }

    public function shouldRemoveHumanSporeWithSuperSoap(FunctionalTester $I): void
    {
        $this->givenPlayerHasSpores(2);

        $this->givenPlayerHasSuperSoap();

        $this->whenPlayerWashesInSink();

        $this->thenPlayerShouldHaveSpores(1, $I);
    }

    public function shouldNotRemoveMushSporeWithSuperSoap(FunctionalTester $I): void
    {
        $this->givenPlayerHasSpores(2);

        $this->givenPlayerHasSuperSoap();

        $this->givenPlayerIsMush();

        $this->whenPlayerWashesInSink();

        $this->thenPlayerShouldHaveSpores(2, $I);
    }

    public function shouldCostOneLessActionPointWithSuperSoap(FunctionalTester $I): void
    {
        $this->givenActionCostIs(2);

        $this->givenPlayerHasSuperSoap();

        $this->whenPlayerTriesToWashInSink();

        $this->thenActionCostShouldBe(1, $I);
    }

    public function shouldMakeAntiquePerfumePlayerImmunized(FunctionalTester $I): void
    {
        $this->givenPlayerHasAntiquePerfumeSkill($I);

        $this->whenPlayerWashesInSink();

        $this->thenPlayerShouldBeImmunized($I);
    }

    private function givenPlayerHasSpores(int $spores): void
    {
        $this->player->setSpores($spores);
    }

    private function givenPlayerHasSuperSoap(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SUPER_SOAPER,
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
            time: new \DateTime()
        );
    }

    private function givenPlayerHasAntiquePerfumeSkill(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::ANTIQUE_PERFUME, $I);
    }

    private function whenPlayerTriesToWashInSink(): void
    {
        $this->washInSinkAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->kitchen,
            player: $this->player,
            target: $this->kitchen
        );
    }

    private function givenActionCostIs(int $actionCost): void
    {
        $this->actionConfig->setActionCost($actionCost);
    }

    private function whenPlayerWashesInSink(): void
    {
        $this->whenPlayerTriesToWashInSink();
        $this->washInSinkAction->execute();
    }

    private function thenPlayerShouldHaveSpores(int $spores, FunctionalTester $I): void
    {
        $I->assertEquals($spores, $this->player->getSpores());
    }

    private function thenActionCostShouldBe(int $actionCost, FunctionalTester $I): void
    {
        $I->assertEquals($actionCost, $this->washInSinkAction->getActionPointCost());
    }

    private function thenPlayerShouldBeImmunized(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::ANTIQUE_PERFUME_IMMUNIZED));
    }
}
