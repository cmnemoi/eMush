<?php

declare(strict_types=1);

namespace Mush\tests\functional\Project;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class TeslaSup2xCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private GameEquipment $turret;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenTurretInRoom();
    }

    public function shouldDoubleMaxTurretCharges(FunctionalTester $I): void
    {   
        $this->givenTurretHasMaxCharges(4);

        $this->whenTeslaSup2xIsActivated($I);

        $this->thenTurretShouldHaveMaxCharges(8, $I);
    }

    public function shouldLoadTurretChargeToMax(FunctionalTester $I): void
    {
        $this->whenTeslaSup2xIsActivated($I);

        $this->thenTurretShouldHaveCharges(8, $I);
    }

    private function givenTurretInRoom(): void
    {
        $this->turret = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::TURRET_COMMAND,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenTurretHasMaxCharges(int $charges): void
    {   
        // nothing to do
    }

    private function whenTeslaSup2xIsActivated(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::TESLA_SUP2X),
            author: $this->chun,
            I: $I,
        );
    }

    private function thenTurretShouldHaveMaxCharges(int $charges, FunctionalTester $I): void
    {   
        $chargeStatus = $this->turret->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $I->assertEquals($charges, $chargeStatus->getMaxChargeOrThrow());
    }

    private function thenTurretShouldHaveCharges(int $charges, FunctionalTester $I): void
    {
        $chargeStatus = $this->turret->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $I->assertEquals($charges, $chargeStatus->getCharge());
    }
}