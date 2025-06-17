<?php

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\CheckRoster;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CheckRosterCest extends AbstractFunctionalTest
{
    private ActionConfig $checkRosterActionConfig;
    private CheckRoster $checkRoster;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private Place $laboratory;
    private GameEquipment $cryoModule;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->checkRosterActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CHECK_ROSTER]);
        $this->checkRoster = $I->grabService(CheckRoster::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $this->cryoModule = $this->givenACryoModuleInLaboratory($I);
    }

    public function testCheckRoster(FunctionalTester $I): void {}

    private function givenACryoModuleInLaboratory(FunctionalTester $I): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::CRYO_MODULE,
            equipmentHolder: $this->laboratory,
            reasons: [],
            time: new \DateTime(),
        );
    }
}
