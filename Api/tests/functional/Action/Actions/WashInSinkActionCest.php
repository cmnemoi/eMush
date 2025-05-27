<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\WashInSink;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
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

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::WASH_IN_SINK]);
        $this->washInSinkAction = $I->grabService(WashInSink::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
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
}
