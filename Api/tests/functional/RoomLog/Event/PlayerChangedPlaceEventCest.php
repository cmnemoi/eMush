<?php

declare(strict_types=1);

namespace Mush\Tests\functional\RoomLog\Event;

use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Event\PlayerChangedPlaceEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerChangedPlaceEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private Place $laboratory;
    private Place $planet;
    private Place $space;
    private Place $patrolShip;
    private Place $medlab;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $this->planet = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::PLANET);
        $this->space = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE);
        $this->patrolShip = $this->createExtraPlace(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN, $I, $this->daedalus);
    }

    public function shouldNotCreateLogsWhenPlayerMovesFromRoomToPlanet(FunctionalTester $I): void
    {
        $this->givenPlayerIsInLaboratory();

        $this->whenPlayerMovesToPlanet();

        $this->thenNoRoomLogsShouldBeCreated($I);
    }

    public function shouldNotCreateLogsWhenPlayerMovesFromPlanetToRoom(FunctionalTester $I): void
    {
        $this->givenPlayerIsOnPlanet();

        $this->whenPlayerMovesToLaboratory();

        $this->thenNoRoomLogsShouldBeCreated($I);
    }

    public function shouldCreateLogsWhenPlayerMovesBetweenTwoRooms(FunctionalTester $I): void
    {
        $this->givenPlayerIsInLaboratory();
        $this->givenMedlabExists($I);

        $this->whenPlayerMovesToMedlab();

        $this->thenRoomLogsShouldBeCreated($I);
    }

    public function shouldNotCreateLogsWhenPlayerMovesFromPatrolShipToRoom(FunctionalTester $I): void
    {
        $this->givenPlayerIsInPatrolShip();

        $this->whenPlayerMovesToLaboratory();

        $this->thenNoRoomLogsShouldBeCreated($I);
    }

    public function shouldNotCreateLogsWhenPlayerMovesFromRoomToPatrolShip(FunctionalTester $I): void
    {
        $this->givenPlayerIsInLaboratory();

        $this->whenPlayerMovesToPatrolShip();

        $this->thenNoRoomLogsShouldBeCreated($I);
    }

    public function shouldNotCreateLogsWhenPlayerMovesFromPatrolShipToPlanet(FunctionalTester $I): void
    {
        $this->givenPlayerIsInPatrolShip();

        $this->whenPlayerMovesToPlanet();

        $this->thenNoRoomLogsShouldBeCreated($I);
    }

    public function shouldNotCreateLogsWhenPlayerMovesFromPatrolShipToSpace(FunctionalTester $I): void
    {
        $this->givenPlayerIsInPatrolShip();

        $this->whenPlayerMovesToSpace();

        $this->thenNoRoomLogsShouldBeCreated($I);
    }

    public function shouldNotCreateLogsWhenPlayerMovesFromSpaceToPatrolShip(FunctionalTester $I): void
    {
        $this->givenPlayerIsInSpace();

        $this->whenPlayerMovesToPatrolShip();

        $this->thenNoRoomLogsShouldBeCreated($I);
    }

    private function givenPlayerIsInLaboratory(): void
    {
        $this->chun->changePlace($this->laboratory);
    }

    private function givenPlayerIsOnPlanet(): void
    {
        $this->chun->changePlace($this->planet);
    }

    private function givenPlayerIsInPatrolShip(): void
    {
        $this->chun->changePlace($this->patrolShip);
    }

    private function givenPlayerIsInSpace(): void
    {
        $this->chun->changePlace($this->space);
    }

    private function givenMedlabExists(FunctionalTester $I): void
    {
        $this->medlab = $this->createExtraPlace(RoomEnum::MEDLAB, $I, $this->daedalus);
    }

    private function whenPlayerMovesToPlanet(): void
    {
        $this->dispatchPlayerChangedPlaceEvent($this->planet);
    }

    private function whenPlayerMovesToLaboratory(): void
    {
        $this->dispatchPlayerChangedPlaceEvent($this->laboratory);
    }

    private function whenPlayerMovesToMedlab(): void
    {
        $this->dispatchPlayerChangedPlaceEvent($this->medlab);
    }

    private function whenPlayerMovesToPatrolShip(): void
    {
        $this->dispatchPlayerChangedPlaceEvent($this->patrolShip);
    }

    private function whenPlayerMovesToSpace(): void
    {
        $this->dispatchPlayerChangedPlaceEvent($this->space);
    }

    private function dispatchPlayerChangedPlaceEvent(Place $newPlace): void
    {
        $oldPlace = $this->chun->getPlace();
        $this->chun->changePlace($newPlace);
        $playerChangedPlaceEvent = new PlayerChangedPlaceEvent(
            player: $this->chun,
            oldPlace: $oldPlace,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($playerChangedPlaceEvent, PlayerChangedPlaceEvent::class);
    }

    private function thenNoRoomLogsShouldBeCreated(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(RoomLog::class, [
            'log' => ActionLogEnum::EXIT_ROOM,
        ]);
        $I->dontSeeInRepository(RoomLog::class, [
            'log' => ActionLogEnum::ENTER_ROOM,
        ]);
    }

    private function thenRoomLogsShouldBeCreated(FunctionalTester $I): void
    {
        $I->seeInRepository(RoomLog::class, [
            'log' => ActionLogEnum::EXIT_ROOM,
        ]);
        $I->seeInRepository(RoomLog::class, [
            'log' => ActionLogEnum::ENTER_ROOM,
        ]);
    }
}
