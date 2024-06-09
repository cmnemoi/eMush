<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Status\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectFinishedEventCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function shouldIncreaseMaxChargesOfPatrolShipWhenPatrolshipExtraAmmoProjectIsFinished(FunctionalTester $I): void
    {
        $patrolShip = $this->givenAPatrolShipWithTenMaxCharges();

        $this->whenPatrolshipExtraAmmoProjectIsFinished($I);

        $this->thenPatrolShipShouldHaveSixteenMaxCharges($patrolShip, $I);
    }

    public function shouldIncreasePatrolShipChargesToMaximumWhenPatrolshipExtraAmmoProjectIsFinished(FunctionalTester $I): void
    {
        $patrolShip = $this->givenAPatrolShipWithTenMaxCharges();

        $this->whenPatrolshipExtraAmmoProjectIsFinished($I);

        $this->thenPatrolShipShouldHaveMaximumCharges($patrolShip, $I);
    }

    private function givenAPatrolShipWithTenMaxCharges(): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function whenPatrolshipExtraAmmoProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PATROLSHIP_EXTRA_AMMO),
            author: $this->player,
            I: $I
        );
    }

    private function thenPatrolShipShouldHaveSixteenMaxCharges(GameEquipment $patrolShip, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: 16,
            actual: $patrolShip->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->getMaxChargeOrThrow(),
        );
    }

    private function thenPatrolShipShouldHaveMaximumCharges(GameEquipment $patrolShip, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $patrolShip->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->getMaxChargeOrThrow(),
            actual: $patrolShip->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->getCharge(),
        );
    }
}
