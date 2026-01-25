<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\CheckSporeLevel;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CheckSporeLevelActionCest extends AbstractFunctionalTest
{
    private CheckSporeLevel $checkSporeLevel;
    private ActionConfig $actionConfig;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::CHECK_SPORE_LEVEL]);

        $this->checkSporeLevel = $I->grabService(CheckSporeLevel::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testCheckSporeLevel(FunctionalTester $I)
    {
        // given kuan ti has 2 spores
        $this->kuanTi->setActionPoint(2)->setSpores(2);

        // given the mycoscan is in the room
        $mycoscan = $this->createEquipment(EquipmentEnum::MYCOSCAN, $this->kuanTi->getPlace());

        // given kuan ti check his spore level
        $this->checkSporeLevel->loadParameters($this->actionConfig, $mycoscan, $this->kuanTi, $mycoscan);
        $I->assertTrue($this->checkSporeLevel->isVisible());
        $this->checkSporeLevel->execute();

        // i should see a log in the room
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->kuanTi->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->kuanTi->getPlayerInfo()->getId(),
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => ActionLogEnum::CHECK_SPORE_LEVEL,
        ]);

        // as well as a hidden one for moderators
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->kuanTi->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->kuanTi->getPlayerInfo()->getId(),
            'visibility' => VisibilityEnum::HIDDEN,
            'log' => ActionLogEnum::USE_MYCOSCAN,
        ]);
    }

    public function testCheckSporeDailyLimit(FunctionalTester $I)
    {
        // given the mycoscan is in the room
        $mycoscan = $this->createEquipment(EquipmentEnum::MYCOSCAN, $this->kuanTi->getPlace());

        // given kuan ti checks his spore level
        $this->checkSporeLevel->loadParameters($this->actionConfig, $mycoscan, $this->kuanTi, $mycoscan);
        $I->assertTrue($this->checkSporeLevel->isVisible());

        // action can be executed
        $I->assertEquals(
            expected: null,
            actual: $this->checkSporeLevel->cannotExecuteReason(),
        );

        $this->checkSporeLevel->execute();

        // action now has a cannotExecuteReason
        $I->assertEquals(
            expected: ActionImpossibleCauseEnum::DAILY_LIMIT_MYCOSCAN,
            actual: $this->checkSporeLevel->cannotExecuteReason(),
        );
    }
}
