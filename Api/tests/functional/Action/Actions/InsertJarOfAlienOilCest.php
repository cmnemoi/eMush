<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\InsertJarOfAlienOil;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class InsertJarOfAlienOilCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    private Action $actionConfig;
    private InsertJarOfAlienOil $action;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->actionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::INSERT_JAR_OF_ALIEN_OIL]);
        $this->action = $I->grabService(InsertJarOfAlienOil::class);
    }

    public function testInsertJarOfAlienOilSuccess(FunctionalTester $I): void
    {
        // given Daedalus has 0 fuel
        $this->player->getDaedalus()->setFuel(0);

        // given there is a fuel tank in the room
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::FUEL_TANK,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        // given player has a jar of alien oil in inventory
        $jarOfAlienOil = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::JAR_OF_ALIEN_OIL,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // when player inserts jar of alien oil in fuel tank
        $this->action->loadParameters($this->actionConfig, $this->player, $jarOfAlienOil);
        $this->action->execute();

        // then jar of alien oil is removed from player's inventory
        $I->assertFalse($this->player->hasEquipmentByName(ToolItemEnum::JAR_OF_ALIEN_OIL));

        // then daedalus has 5 more fuel
        $I->assertEquals(5, $this->player->getDaedalus()->getFuel());
    }
}
