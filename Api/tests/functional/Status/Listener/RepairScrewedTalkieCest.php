<?php

namespace Mush\Tests\functional\Status\Listener;

use Mush\Action\Actions\Repair;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RepairScrewedTalkieCest extends AbstractFunctionalTest
{
    private ActionConfig $repairConfig;
    private Repair $repairAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->repairConfig = $I->grabEntityFromRepository(ActionConfig::class, [
            'name' => 'repair_percent_25',
        ]);
        $this->repairConfig->setSuccessRate(101);
        $this->repairAction = $I->grabService(Repair::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testRepairTalkieRemovesBrokenAndScrewedStatus(FunctionalTester $I)
    {
        // given Chun has a talkie
        $talkie = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::WALKIE_TALKIE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given this talkie is broken
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $talkie,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun has screwed status from Kuan Ti
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::TALKIE_SCREWED,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
            target: $this->chun,
        );

        // when Chun repairs her talkie
        $this->repairAction->loadParameters($this->repairConfig, $this->chun, $talkie);
        $this->repairAction->execute();

        // then the talkie is no longer broken
        $I->assertFalse($talkie->hasStatus(EquipmentStatusEnum::BROKEN));

        // then Chun no longer has screwed status
        $I->assertFalse($this->chun->hasStatus(PlayerStatusEnum::TALKIE_SCREWED));
    }
}
