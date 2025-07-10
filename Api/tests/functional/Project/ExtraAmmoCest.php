<?php

declare(strict_types=1);

namespace Mush\tests\functional\Project;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ExtraAmmoCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function shouldGiveExtraAmmoToAPatrolShip(FunctionalTester $I): void
    {
        $tamarin = $this->givenThereIsTamarinInTheRoom();

        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PATROLSHIP_EXTRA_AMMO),
            author: $this->chun,
            I: $I
        );

        $this->thenShipShouldHaveXAmmo($tamarin, 16, $I);
    }

    private function givenThereIsWallisInTheRoom(): GameEquipment
    {
        $ship = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $ship->setName(EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS);

        return $ship;
    }

    private function givenThereIsJujubeInTheRoom(): GameEquipment
    {
        $ship = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $ship->setName(EquipmentEnum::PATROL_SHIP_ALPHA_JUJUBE);

        return $ship;
    }

    private function givenThereIsLonganeInTheRoom(): GameEquipment
    {
        $ship = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $ship->setName(EquipmentEnum::PATROL_SHIP_ALPHA_LONGANE);

        return $ship;
    }

    private function givenThereIsTamarinInTheRoom(): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenThereIsEpicureInTheRoom(): GameEquipment
    {
        $ship = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $ship->setName(EquipmentEnum::PATROL_SHIP_BRAVO_EPICURE);

        return $ship;
    }

    private function givenThereIsPlantonInTheRoom(): GameEquipment
    {
        $ship = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $ship->setName(EquipmentEnum::PATROL_SHIP_BRAVO_PLANTON);

        return $ship;
    }

    private function givenThereIsSocrateInTheRoom(): GameEquipment
    {
        $ship = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $ship->setName(EquipmentEnum::PATROL_SHIP_BRAVO_SOCRATE);

        return $ship;
    }

    private function thenShipShouldHaveXAmmo(GameEquipment $ship, $amount, FunctionalTester $I): void
    {
        $I->assertEquals($amount, $ship->getChargeStatusByName(EquipmentStatusEnum::ELECTRIC_CHARGES)->getCharge(), 'Ship ' . $ship->getName() . ' should have ' . $amount . ' charges.');
    }
}
