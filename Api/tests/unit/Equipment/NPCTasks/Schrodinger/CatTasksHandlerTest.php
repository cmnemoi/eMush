<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Equipment\NPCTasks\Schrodinger;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\NPCTasks\Schrodinger\CatTasksHandler;
use Mush\Equipment\NPCTasks\Schrodinger\MoveAwayFromPeopleTask;
use Mush\Equipment\NPCTasks\Schrodinger\MoveInRandomAdjacentRoomTask;
use Mush\Equipment\NPCTasks\Schrodinger\MoveTowardsOwnerTask;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService as D100Roll;
use Mush\Game\Service\Random\FakeGetRandomIntegerService as GetRandomInteger;
use Mush\Game\Service\Random\GetRandomElementsFromArrayService as GetRandomElementsFromArray;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Service\FindNextRoomTowardsConditionService;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Tests\unit\Equipment\TestDoubles\MoveEquipmentService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CatTasksHandlerTest extends TestCase
{
    private EventServiceInterface $eventService;
    private MoveEquipmentService $moveEquipmentService;

    private Daedalus $daedalus;

    private Place $laboratory;
    private Place $medlab;
    private Place $frontCorridor;

    private Player $raluca;
    private GameItem $cat;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->eventService = self::createStub(EventServiceInterface::class);
        $this->moveEquipmentService = new MoveEquipmentService();

        $this->daedalus = DaedalusFactory::createDaedalus();

        $this->laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $this->medlab = $this->medlab();
        Door::createFromRooms($this->laboratory, $this->medlab);

        $this->frontCorridor = $this->frontCorridor();
        Door::createFromRooms($this->laboratory, $this->frontCorridor);

        $this->raluca = PlayerFactory::createPlayerByNameAndPlace(CharacterEnum::RALUCA, $this->laboratory);
        StatusFactory::createStatusByNameForHolder(name: PlayerStatusEnum::CAT_OWNER, holder: $this->raluca);

        $this->cat = GameEquipmentFactory::createItemByNameForHolder(ItemEnum::SCHRODINGER, $this->laboratory);
    }

    public function testCatShouldMoveTowardsAliveRalucaOnSuccess(): void
    {
        $this->givenRalucaIsInFrontCorridor();

        $this->whenCatActsWithD100Roll(new D100Roll(isSuccessful: true));

        $this->thenCatShouldBeInFrontCorridor();
    }

    public function testCatShouldNotMoveTowardsAliveRalucaOnFailure(): void
    {
        $this->givenRalucaIsInFrontCorridor();

        $this->whenCatActsWithD100Roll(new D100Roll(isSuccessful: false));

        $this->thenCatShouldNotBeInFrontCorridor();
    }

    public function testCatShouldStayInAliveRalucaPlace(): void
    {
        $this->whenCatActsWithD100Roll(new D100Roll());

        $this->thenCatShouldBeInLaboratory();
    }

    public function testCatShouldStayInEmptyRoomWithDeadRalucaOnSuccess(): void
    {
        $this->givenRalucaIsDead();

        $this->whenCatActsWithD100Roll(new D100Roll(isSuccessful: true));

        $this->thenCatShouldBeInLaboratory();
    }

    public function testCatShouldMoveRandomlyFromEmptyRoomWithDeadRalucaOnFailure(): void
    {
        $this->givenRalucaIsDead();

        $this->whenCatActsWithD100Roll(new D100Roll(isSuccessful: false));

        $this->thenCatShouldBeInMedlab();
    }

    public function testCatShouldMoveRandomlyFromNonEmptyRoomWithDeadRalucaOnFailures(): void
    {
        $this->givenRalucaIsDead();
        $this->givenChunIsInLaboratory();

        $this->whenCatActsWithD100Roll(new D100Roll()->makeFail());

        $this->thenCatShouldNotBeInLaboratory();
    }

    public function testCatShouldMoveAwayFromPlayersFromNonEmptyRoomWithDeadRalucaOnSuccess(): void
    {
        $this->givenRalucaIsDead();
        $this->givenChunIsInLaboratory();
        $this->givenPaolaIsInMedlab();

        $this->whenCatActsWithD100Roll(new D100Roll()->makeSuccessful());

        $this->thenCatShouldBeInFrontCorridor();
    }

    private function givenRalucaIsInFrontCorridor(): void
    {
        $this->raluca->changePlace($this->frontCorridor);
    }

    private function givenRalucaIsDead(): void
    {
        $this->raluca->kill();
    }

    private function givenChunIsInLaboratory(): void
    {
        PlayerFactory::createPlayerByNameAndPlace(CharacterEnum::CHUN, $this->laboratory);
    }

    private function givenPaolaIsInMedlab(): void
    {
        PlayerFactory::createPlayerByNameAndPlace(CharacterEnum::PAOLA, $this->medlab);
    }

    private function whenCatActsWithD100Roll(D100Roll $d100Roll): void
    {
        $catTasksHandler = new CatTasksHandler(
            d100Roll: $d100Roll,
            moveTowardsOwner: $this->moveTowardsOwnerTask(),
            moveAwayFromPeople: $this->moveAwayFromPeopleTask(),
            moveInRandomAdjacentRoom: $this->moveInRandomAdjacentRoomTask(),
        );
        $catTasksHandler->execute($this->cat, new \DateTime());
    }

    private function thenCatShouldBeInFrontCorridor(): void
    {
        self::assertTrue($this->cat->getPlace()->equals($this->frontCorridor));
    }

    private function thenCatShouldNotBeInFrontCorridor(): void
    {
        self::assertTrue($this->cat->getPlace()->notEquals($this->frontCorridor));
    }

    private function thenCatShouldBeInLaboratory(): void
    {
        self::assertTrue($this->cat->getPlace()->equals($this->laboratory));
    }

    private function thenCatShouldBeInMedlab(): void
    {
        self::assertTrue($this->cat->getPlace()->equals($this->medlab));
    }

    private function thenCatShouldNotBeInLaboratory(): void
    {
        self::assertTrue($this->cat->getPlace()->notEquals($this->laboratory));
    }

    private function moveTowardsOwnerTask(): MoveTowardsOwnerTask
    {
        return new MoveTowardsOwnerTask(
            eventService: $this->eventService,
            findNextRoomTowardsCondition: new FindNextRoomTowardsConditionService(),
            gameEquipmentService: $this->moveEquipmentService,
            getRandomElementsFromArray: new GetRandomElementsFromArray(new GetRandomInteger(result: 0)),
        );
    }

    private function moveAwayFromPeopleTask(): MoveAwayFromPeopleTask
    {
        return new MoveAwayFromPeopleTask(
            eventService: $this->eventService,
            findNextRoomTowardsCondition: new FindNextRoomTowardsConditionService(),
            gameEquipmentService: $this->moveEquipmentService,
            getRandomElementsFromArray: new GetRandomElementsFromArray(new GetRandomInteger(result: 0)),
        );
    }

    private function moveInRandomAdjacentRoomTask(): MoveInRandomAdjacentRoomTask
    {
        return new MoveInRandomAdjacentRoomTask(
            eventService: $this->eventService,
            gameEquipmentService: $this->moveEquipmentService,
            getRandomElementsFromArray: new GetRandomElementsFromArray(new GetRandomInteger(result: 0)),
        );
    }

    private function frontCorridor(): Place
    {
        $frontCorridor = Place::createRoomByNameInDaedalus(RoomEnum::FRONT_CORRIDOR, $this->daedalus);
        new \ReflectionProperty($frontCorridor, 'id')->setValue($frontCorridor, random_int(1, PHP_INT_MAX));

        return $frontCorridor;
    }

    private function medlab(): Place
    {
        $medlab = Place::createRoomByNameInDaedalus(RoomEnum::MEDLAB, $this->daedalus);
        new \ReflectionProperty($medlab, 'id')->setValue($medlab, random_int(1, PHP_INT_MAX));

        return $medlab;
    }
}
