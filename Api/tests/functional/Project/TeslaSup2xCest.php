<?php

declare(strict_types=1);

namespace Mush\tests\functional\Project;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TeslaSup2xCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private GameEquipment $turret;
    private GameEquipment $blaster;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->givenTurretInRoom();
        $this->givenBlasterInRoom();
    }

    public function shouldDoubleMaxTurretCharges(FunctionalTester $I): void
    {
        $this->givenTurretHasMaxCharges(4);

        $this->whenTeslaSup2xIsActivated($I);

        $this->thenTurretShouldHaveMaxCharges(8, $I);
    }

    public function shouldNotChangeOtherEquipmentMaxCharges(FunctionalTester $I): void
    {
        $this->givenBlasterHasMaxCharges(3);

        $this->whenTeslaSup2xIsActivated($I);

        $this->thenBlasterShouldHaveMaxCharges(3, $I);
    }

    public function shouldLoadTurretChargesToMax(FunctionalTester $I): void
    {
        $this->whenTeslaSup2xIsActivated($I);

        $this->thenTurretShouldHaveCharges(8, $I);
    }

    public function shouldNotChangeOtherEquipmentCharges(FunctionalTester $I): void
    {
        $this->givenBlasterHasCharges(3);

        $this->whenTeslaSup2xIsActivated($I);

        $this->thenBlasterShouldHaveCharges(3, $I);
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

    private function givenBlasterInRoom(): void
    {
        $this->blaster = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BLASTER,
            equipmentHolder: $this->kuanTi,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenTurretHasMaxCharges(int $charges): void
    {
        // nothing to do
    }

    private function givenBlasterHasMaxCharges(int $charges): void
    {
        // nothing to do
    }

    private function givenBlasterHasCharges(int $charges): void
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

    private function thenBlasterShouldHaveMaxCharges(int $charges, FunctionalTester $I): void
    {
        $chargeStatus = $this->blaster->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $I->assertEquals($charges, $chargeStatus->getMaxChargeOrThrow());
    }

    private function thenBlasterShouldHaveCharges(int $charges, FunctionalTester $I): void
    {
        $chargeStatus = $this->blaster->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES);
        $I->assertEquals($charges, $chargeStatus->getCharge());
    }
}
