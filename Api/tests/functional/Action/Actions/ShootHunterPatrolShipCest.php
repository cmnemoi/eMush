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
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
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
        $this->givenBlasterGunProjectIsFinished($I);
        $this->givenPatrolShipDealsDamagePoints(1);
        $this->givenChunHas100PercentChanceToHit();

        $this->whenChunShootsWithBlasterGunOnAHunterWithSixHealthPoints();

        $this->thenHunterShouldHaveLostTwoHealthPoints($I);
    }

    public function shouldLogCorrectlyWithBlasterGunProject(FunctionalTester $I): void
    {
        $this->givenBlasterGunProjectIsFinished($I);
        $this->givenPatrolShipDealsDamagePoints(5);
        $this->givenChunHas100PercentChanceToHit();

        $this->whenChunShootsWithBlasterGunOnAHunterWithSixHealthPoints();

        $this->thenIShouldNotSeeShootHunterSuccessLog($I);
        $this->thenIShouldSeeHunterDeathLog($I);
    }

    private function givenBlasterGunProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PATROLSHIP_BLASTER_GUN),
            author: $this->player,
            I: $I,
        );
    }

    private function givenPatrolShipDealsDamagePoints(int $damage): void
    {
        /** @var Weapon $patrolShipWeaponMechanic */
        $patrolShipWeaponMechanic = $this->patrolShip->getMechanicByNameOrThrow(EquipmentMechanicEnum::WEAPON);
        $patrolShipWeaponMechanic->setBaseDamageRange([$damage => 1]);
    }

    private function givenChunHas100PercentChanceToHit(): void
    {
        $this->actionConfig->setSuccessRate(100);
    }

    private function whenChunShootsWithBlasterGunOnAHunterWithSixHealthPoints(): void
    {
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunter->setHealth(6);

        $this->shootHunterPatrolShipAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->patrolShip,
            player: $this->chun,
            target: $hunter,
        );
        $this->shootHunterPatrolShipAction->execute();
    }

    private function thenHunterShouldHaveLostTwoHealthPoints(FunctionalTester $I): void
    {
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $I->assertEquals(4, $hunter->getHealth());
    }

    private function thenIShouldNotSeeShootHunterSuccessLog(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::PATROL_SHIP_ALPHA_TAMARIN,
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'log' => ActionLogEnum::SHOOT_HUNTER_PATROL_SHIP_SUCCESS,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    private function thenIShouldSeeHunterDeathLog(FunctionalTester $I): void
    {
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => RoomEnum::PATROL_SHIP_ALPHA_TAMARIN,
                'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
                'playerInfo' => $this->chun->getPlayerInfo(),
                'log' => LogEnum::HUNTER_DEATH_PATROL_SHIP,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }
}
