<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\ShootHunterPatrolShip;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ShootHunterPatrolShipCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private ShootHunterPatrolShip $shootHunterPatrolShipAction;

    private GameEquipment $patrolShip;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::SHOOT_HUNTER_PATROL_SHIP]);
        $this->shootHunterPatrolShipAction = $I->grabService(ShootHunterPatrolShip::class);

        /** @var GameEquipmentServiceInterface $gameEquipmentService */
        $gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        /** @var EventServiceInterface $eventService */
        $eventService = $I->grabService(EventServiceInterface::class);

        // given I have in Patrol Ship in space battle
        $patrolShipPlace = $this->createExtraPlace(placeName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN, I: $I, daedalus: $this->daedalus);
        $this->patrolShip = $gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $patrolShipPlace,
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun is in the patrol ship
        $this->chun->changePlace($patrolShipPlace);

        // given some hunters are spawn
        $event = new HunterPoolEvent(
            $this->daedalus,
            [],
            new \DateTime()
        );
        $eventService->callEvent($event, HunterPoolEvent::UNPOOL_HUNTERS);
    }

    public function shouldMakeOneMoreDamagePointWithBlasterGunProject(FunctionalTester $I): void
    {
        // given Blaster Gun project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PATROLSHIP_BLASTER_GUN),
            author: $this->player,
            I: $I,
        );

        // given patrol ship deals 1 damage point
        /** @var Weapon $patrolShipWeaponMechanic */
        $patrolShipWeaponMechanic = $this->patrolShip->getMechanicByNameOrThrow(EquipmentMechanicEnum::WEAPON);
        $patrolShipWeaponMechanic->setBaseDamageRange([1 => 1]);

        // given Chun has a 100% chance to hit
        $this->actionConfig->setSuccessRate(100);

        // when Chun shoots with Blaster Gun on a hunter with 6 health points
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHealth(6);

        $this->shootHunterPatrolShipAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->patrolShip,
            player: $this->chun,
            target: $hunter,
        );
        $this->shootHunterPatrolShipAction->execute();

        // then hunter should have lost 2 health points
        $I->assertEquals(4, $hunter->getHealth());
    }
}
