<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Sabotage;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class SabotageCest extends AbstractFunctionalTest
{
    private Action $sabotageActionConfig;
    private Sabotage $sabotageAction;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);

        $this->sabotageActionConfig = $I->grabEntityFromRepository(Action::class, ['name' => 'sabotage_percent_12']);
        $this->sabotageAction = $I->grabService(Sabotage::class);
    }

    public function testSabotageIsNotExecutableIfPatrolShipNotInARoom(FunctionalTester $I): void
    {
        // given a pasiphae in its room
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::PASIPHAE));
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        // given player is in pasiphae room
        $this->player->changePlace($pasiphae->getPlace());

        // when player try to sabotage pasiphae
        $this->sabotageAction->loadParameters($this->sabotageActionConfig, $this->player, $pasiphae);

        // then sabotage is not executable
        $I->assertEquals(ActionImpossibleCauseEnum::NOT_A_ROOM, $this->sabotageAction->cannotExecuteReason());
    }
}
