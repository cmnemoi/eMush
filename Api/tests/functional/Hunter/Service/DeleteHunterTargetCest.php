<?php

declare(strict_types=1);

namespace Mush\tests\functional\Hunter\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
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
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
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
    private PlayerServiceInterface $playerService;
    private DaedalusServiceInterface $daedalusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->hunterService = $I->grabService(HunterServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->daedalusService = $I->grabService(DaedalusServiceInterface::class);

        $this->givenAPatrolShipInBattle($I);
    }

    public function shouldWorkWhenHunterAimsAtAPatrolShip(FunctionalTester $I): void
    {
        $this->givenHuntersAreAttacking(number: 1);

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

        // then hunter target should be properly deleted
        $I->dontSeeInRepository(HunterTarget::class);
    }

    public function shouldWorkWhenHunterAimsAtHunter(FunctionalTester $I): void
    {
        $this->givenHuntersAreAttacking(number: 2);

        $this->givenHunterIsAimingAtHunter($I);

        // when hunter is deleted
        $attackedHunter = $this->daedalus
            ->getHuntersAroundDaedalus()
            ->filter(fn (Hunter $hunter) => $hunter->notEquals($this->hunter))
            ->first();
        $attackedHunterId = $attackedHunter->getId();

        $this->hunterService->delete([$attackedHunter]);

        // then hunter should be properly deleted
        $I->dontSeeInRepository(Hunter::class, ['id' => $attackedHunterId]);

        // then hunter target should be properly deleted
        $I->dontSeeInRepository(HunterTarget::class);
    }

    public function shouldWorkWhenHunterAimsAtPlayer(FunctionalTester $I): void
    {
        $this->givenHuntersAreAttacking(number: 1);

        $this->givenHunterIsAimingAtPlayer($I);

        // when player is deleted
        $playerId = $this->player->getId();
        $this->playerService->delete($this->player);

        // then player should be properly deleted
        $I->dontSeeInRepository(entity: Player::class, params: ['id' => $playerId]);

        // then hunter target should be properly deleted
        $I->dontSeeInRepository(HunterTarget::class);
    }

    public function shouldWorkWhenHunterAimsAtDaedalus(FunctionalTester $I): void
    {
        $this->givenHuntersAreAttacking(number: 1);

        $this->givenHunterIsAimingAtDaedalus($I);

        // when daedalus is deleted
        $this->daedalusService->closeDaedalus($this->daedalus, [EndCauseEnum::DAEDALUS_DESTROYED], new \DateTime());

        // then daedalus should be properly deleted
        $I->dontSeeInRepository(entity: Daedalus::class);

        // then hunter target should be properly deleted
        $I->dontSeeInRepository(HunterTarget::class);
    }

    private function givenHuntersAreAttacking(int $number): void
    {
        $this->daedalus->setHunterPoints($number * 10);
        $this->hunterService->unpoolHunters(
            $this->daedalus,
            tags: [],
            time: new \DateTime()
        );

        $this->hunter = $this->daedalus->getHuntersAroundDaedalus()->first();
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

        $this->hunter->setTarget($hunterTarget);
        $I->haveInRepository($this->hunter);
    }

    private function givenHunterIsAimingAtHunter(FunctionalTester $I): void
    {
        $attackedHunter = $this->daedalus
            ->getHuntersAroundDaedalus()
            ->filter(fn (Hunter $hunter) => $hunter->notEquals($this->hunter))
            ->first();

        $hunterTarget = new HunterTarget($this->hunter);
        $I->haveInRepository($hunterTarget);
        $hunterTarget->setTargetEntity($attackedHunter);

        $this->hunter->setTarget($hunterTarget);
        $I->haveInRepository($this->hunter);
    }

    private function givenHunterIsAimingAtPlayer(FunctionalTester $I): void
    {
        $hunterTarget = new HunterTarget($this->hunter);
        $I->haveInRepository($hunterTarget);
        $hunterTarget->setTargetEntity($this->player);

        $this->hunter->setTarget($hunterTarget);
        $I->haveInRepository($this->hunter);
    }

    private function givenHunterIsAimingAtDaedalus(FunctionalTester $I): void
    {
        $hunterTarget = new HunterTarget($this->hunter);
        $I->haveInRepository($hunterTarget);
        $hunterTarget->setTargetEntity($this->daedalus);

        $this->hunter->setTarget($hunterTarget);
        $I->haveInRepository($this->hunter);
    }
}
