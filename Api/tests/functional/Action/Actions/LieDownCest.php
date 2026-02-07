<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\LieDown;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class LieDownCest extends AbstractFunctionalTest
{
    private LieDown $lieDownAction;
    private ActionConfig $lieDownActionConfig;
    private GameEquipment $gameEquipment;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->lieDownAction = $I->grabService(LieDown::class);
        $this->lieDownActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::LIE_DOWN]);
    }

    public function testCanLieDownOnBed(FunctionalTester $I)
    {
        $this->gameEquipment = $this->createEquipment(EquipmentEnum::BED, $this->player->getPlace());

        $this->whenPlayerLiesDown();

        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::LYING_DOWN));
    }

    public function testCanLieDownOnSofa(FunctionalTester $I)
    {
        $this->gameEquipment = $this->createEquipment(EquipmentEnum::SWEDISH_SOFA, $this->player->getPlace());

        $this->whenPlayerLiesDown();

        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::LYING_DOWN));
    }

    public function testLieDownPrintsAPublicLog(FunctionalTester $I)
    {
        $this->gameEquipment = $this->createEquipment(EquipmentEnum::BED, $this->player->getPlace());

        $this->whenPlayerLiesDown();

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::LIE_DOWN,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testLieDownLogIsPrivateWhenNinja(FunctionalTester $I)
    {
        $this->gameEquipment = $this->createEquipment(EquipmentEnum::BED, $this->player->getPlace());
        $this->createStatusOn(PlayerStatusEnum::IS_ANONYMOUS, $this->player);

        $this->whenPlayerLiesDown();

        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::LIE_DOWN,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    private function whenPlayerTriesToLieDown(): void
    {
        $this->lieDownAction->loadParameters(
            actionConfig: $this->lieDownActionConfig,
            actionProvider: $this->gameEquipment,
            player: $this->player,
            target: $this->gameEquipment,
        );
    }

    private function whenPlayerLiesDown(): void
    {
        $this->whenPlayerTriesToLieDown();
        $this->lieDownAction->execute();
    }
}
