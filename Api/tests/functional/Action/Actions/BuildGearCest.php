<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Build;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class BuildGearCest extends AbstractFunctionalTest
{
    private Build $buildAction;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->buildAction = $I->grabService(Build::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testBuildGear(FunctionalTester $I)
    {
        $sniperHelmetBlueprint = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: 'sniper_helmet_blueprint',
            equipmentHolder: $this->player1->getPlace(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );

        $metal = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->player1->getPlace(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );
        $plastic = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::PLASTIC_SCRAPS,
            equipmentHolder: $this->player1->getPlace(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );

        $I->assertEmpty($this->player1->getModifiers());
        $I->assertEmpty($this->player1->getEquipments());
        $I->assertCount(3, $this->player1->getPlace()->getEquipments());

        $buildActionEntity = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::BUILD]);

        $this->buildAction->loadParameters($buildActionEntity, $this->player1, $sniperHelmetBlueprint);

        $I->assertTrue($this->buildAction->isVisible());

        $this->buildAction->execute();

        $I->assertCount(1, $this->player1->getEquipments());
        $I->assertCount(2, $this->player1->getModifiers());
    }
}
