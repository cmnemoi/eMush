<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\PlayWithDogFake;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayWithDogFakeCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private PlayWithDogFake $playWithDog;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private EventServiceInterface $eventService;
    private GameItem $pavlov;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::PLAY_WITH_DOG->value]);
        $this->playWithDog = $I->grabService(PlayWithDogFake::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);

        $this->pavlov = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::PAVLOV,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $this->createExtraPlace(RoomEnum::REAR_ALPHA_STORAGE, $I, $this->daedalus);
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::FUEL_TANK,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::REAR_ALPHA_STORAGE),
            reasons: [],
            time: new \DateTime()
        );
        $this->createExtraPlace(RoomEnum::REAR_BRAVO_STORAGE, $I, $this->daedalus);
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::FUEL_TANK,
            equipmentHolder: $this->daedalus->getPlaceByName(RoomEnum::REAR_BRAVO_STORAGE),
            reasons: [],
            time: new \DateTime()
        );
    }

    public function shouldNotBeExecutableIfPlayerIsGermaphobe(FunctionalTester $I): void
    {
        $this->givenPlayerIsGermaphobe();

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::PLAYER_IS_GERMAPHOBIC, $I);
    }

    public function shouldNotBeExecutableIfDaedalusIsTraveling(FunctionalTester $I): void
    {
        $this->givenDaedalusIsTraveling();

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::DOG_IS_SEASICK, $I);
    }

    public function shouldNotBeExecutableIfHuntersAreAttacking(FunctionalTester $I): void
    {
        $this->givenDaedalusIsBeingAttacked($I);

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::DOG_IS_WORRIED, $I);
    }

    public function shouldNotBeExecutableIfFuelTanksAreBroken(FunctionalTester $I): void
    {
        $this->givenRearBravoStorageFuelTankIsBroken();

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::DISTRACTED_BY_BROKEN_FUEL_TANK, $I);
    }

    public function shouldNotBeExecutableIfSchrodingerInRoom(FunctionalTester $I): void
    {
        $this->givenSchrodingerInRoom();

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::BOTHERING_CAT, $I);
    }

    public function shouldNotBeExecutableIfFoodInRoom(FunctionalTester $I): void
    {
        $this->givenRationInRoom();

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::DISTRACTED_BY_FOOD, $I);
    }

    public function shouldNotBeExecutableIfPlantInRoom(FunctionalTester $I): void
    {
        $this->givenBananaTreeInRoom();

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::DISTRACTED_BY_PLANT, $I);
    }

    public function shouldNotBeExecutableWithoutMadKubeInInventory(FunctionalTester $I): void
    {
        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::NEED_A_BALL, $I);
    }

    public function shouldNotBeExecutableIfDaedalusIsInOrbit(FunctionalTester $I): void
    {
        $this->givenDaedalusIsInOrbit();

        $this->givenPlayerHasAMadKube();

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::WANTS_WALKIES, $I);
    }

    public function shouldNotBeExecutableIfInventoryFull(FunctionalTester $I): void
    {
        $this->givenPlayerHasAMadKube();
        $this->givenPlayerHasAMadKube();
        $this->givenPlayerHasAMadKube();

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::HANDS_FULL, $I);
    }

    public function shouldNotBeExecutableIfInStorage(FunctionalTester $I): void
    {
        $this->givenPlayerHasAMadKube();

        $this->givenPavlovAndPlayerAreInRoom(RoomEnum::FRONT_STORAGE, $I);

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::ROOM_TOO_MESSY, $I);
    }

    public function shouldNotBeExecutableIfInTurret(FunctionalTester $I): void
    {
        $this->givenPlayerHasAMadKube();

        $this->givenPavlovAndPlayerAreInRoom(RoomEnum::REAR_ALPHA_TURRET, $I);

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::ROOM_TOO_SMALL, $I);
    }

    public function shouldNotBeExecutableIfInCorridor(FunctionalTester $I): void
    {
        $this->givenPlayerHasAMadKube();

        $this->givenPavlovAndPlayerAreInRoom(RoomEnum::FRONT_CORRIDOR, $I);

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::ROOM_TOO_TIGHT, $I);
    }

    public function shouldNotBeExecutableIfOtherPlayersInRoom(FunctionalTester $I): void
    {
        // Kuan Ti is created during parent::_before, thus is already present
        $this->givenPlayerHasAMadKube();

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::PLAYTIME_SOLO, $I);
    }

    public function shouldNotBeExecutableIfNotInEngineRoom(FunctionalTester $I): void
    {
        $this->givenPlayerHasAMadKube();

        $this->givenPavlovAndPlayerAreInRoom(RoomEnum::BRIDGE, $I);

        $this->whenPlayerTriesToPlayWithDog();

        $this->thenActionShouldNotBeExecutableBecauseOfCause(ActionImpossibleCauseEnum::ROOM_TOO_SAD, $I);
    }

    public function shouldDeletePavlovIfSuccessful(FunctionalTester $I): void
    {
        $this->givenPlayerHasAMadKube();

        $this->givenPavlovAndPlayerAreInRoom(RoomEnum::ENGINE_ROOM, $I);

        $this->whenPlayerPlaysWithDog();

        $this->thenPavlovHasDisappeared($I);
    }

    private function givenPlayerIsGermaphobe(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::GERMAPHOBE,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenDaedalusIsTraveling(): void
    {
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::TRAVELING,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenDaedalusIsInOrbit(): void
    {
        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenDaedalusIsBeingAttacked(FunctionalTester $I): void
    {
        $event = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime()
        );
        $this->eventService->callEvent($event, HunterPoolEvent::UNPOOL_HUNTERS);
    }

    private function givenRearBravoStorageFuelTankIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->daedalus->getPlaceByName(RoomEnum::REAR_BRAVO_STORAGE)->getEquipmentByNameOrThrow(EquipmentEnum::FUEL_TANK),
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenSchrodingerInRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenRationInRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GameRationEnum::STANDARD_RATION,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenBananaTreeInRoom(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerHasAMadKube(): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::MAD_KUBE,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenPavlovAndPlayerAreInRoom(string $room, FunctionalTester $I): void
    {
        $this->createExtraPlace($room, $I, $this->daedalus);
        $this->player->setPlace($this->daedalus->getPlaceByName($room));
        $this->pavlov->setHolder($this->daedalus->getPlaceByName($room));
    }

    private function whenPlayerTriesToPlayWithDog(): void
    {
        $this->playWithDog->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->pavlov,
            player: $this->player,
            target: $this->pavlov,
        );
    }

    private function whenPlayerPlaysWithDog(): void
    {
        $this->whenPlayerTriesToPlayWithDog();
        $this->playWithDog->execute();
    }

    private function thenActionShouldNotBeExecutableBecauseOfCause(string $cause, FunctionalTester $I): void
    {
        $I->assertEquals($cause, $this->playWithDog->cannotExecuteReason());
    }

    private function thenPavlovHasDisappeared(FunctionalTester $I): void
    {
        $I->assertFalse($this->daedalus->getPlaceByName(RoomEnum::ENGINE_ROOM)->hasEquipmentByName(ItemEnum::PAVLOV));
    }
}
