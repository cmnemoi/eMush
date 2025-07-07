<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\CheckSporeLevel;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
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
        $gameEquipment = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::MYCOSCAN,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given kuan ti check his spore level
        $this->checkSporeLevel->loadParameters($this->actionConfig, $gameEquipment, $this->kuanTi, $gameEquipment);
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
    }
}
