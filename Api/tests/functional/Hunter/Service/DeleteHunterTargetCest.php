<?php

declare(strict_types=1);

namespace Mush\tests\functional\Hunter\Service;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterTarget;
use Mush\Hunter\Service\HunterServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DeleteHunterTargetCest extends AbstractFunctionalTest
{
    private Hunter $hunter;
    private GameEquipment $patrolShip;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private HunterServiceInterface $hunterService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->hunterService = $I->grabService(HunterServiceInterface::class);

        $this->givenOneHunterIsAttacking();

        $this->givenAPatrolShipInBattle($I);
    }

    public function shouldWorkWhenHunterAimsAtAPatrolShip(FunctionalTester $I): void
    {
        $this->givenHunterIsAimingAtAPatrolShip($I);

        // when patrol ship is destroyed
        $event = new InteractWithEquipmentEvent(
            equipment: $this->patrolShip,
            author: null,
            visibility: VisibilityEnum::HIDDEN,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($event, EquipmentEvent::EQUIPMENT_DESTROYED);

        // then patrol ship should be properly deleted
        $I->dontSeeInRepository(entity: GameEquipment::class);
    }

    private function givenOneHunterIsAttacking(): void
    {
        $this->hunterService->unpoolHunters(
            $this->daedalus,
            tags: [],
            time: new \DateTime()
        );

        $this->hunter = $this->daedalus->getAttackingHunters()->first();
    }

    private function givenAPatrolShipInBattle(FunctionalTester $I): void
    {
        $this->createExtraPlace(RoomEnum::PASIPHAE, $I, $this->daedalus);
        $this->patrolShip = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PASIPHAE,
            equipmentHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::PASIPHAE),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenHunterIsAimingAtAPatrolShip(FunctionalTester $I): void
    {
        $hunterTarget = new HunterTarget($this->hunter);
        $I->haveInRepository($hunterTarget);
        $hunterTarget->setTargetEntity($this->patrolShip);
    }
}
