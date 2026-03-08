<?php

namespace Mush\Tests\functional\Equipment\Listener;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlantCycleEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $equipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->equipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenThereIsAGardenInDaedalus($I);
    }

    public function youngPlantShouldGrowByOneCycle(FunctionalTester $I)
    {
        $bananaTree = $this->givenABananaTreeWithMaturationCycleInPlace(RoomEnum::HYDROPONIC_GARDEN, 1, $I);

        $this->whenCycleChangesForBananaTree($bananaTree);

        $this->thenBananaTreeShouldBe(2, $bananaTree, $I);
    }

    public function youngPlantShouldLoseYoungStatusWhenMaturationTimeIsReached(FunctionalTester $I)
    {
        $bananaTree = $this->givenABananaTreeWithMaturationCycleInPlace(RoomEnum::HYDROPONIC_GARDEN, 31, $I);

        $this->whenCycleChangesForBananaTree($bananaTree);

        $this->thenBananaTreeShouldBeAdult($bananaTree, $I);

        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $bananaTree->getPlace()->getName(),
                'log' => PlantLogEnum::PLANT_MATURITY,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function testYoungPlantDayChange(FunctionalTester $I)
    {
        $bananaTree = $this->givenABananaTreeWithMaturationCycleInPlace(RoomEnum::HYDROPONIC_GARDEN, 1, $I);

        $this->whenDayChangesForBananaTree($bananaTree);

        $this->thenTheresNoBananaInRoom($bananaTree->getPlace(), $I);
    }

    public function testAlmostMaturePlantDayChange(FunctionalTester $I)
    {
        $bananaTree = $this->givenABananaTreeWithMaturationCycleInPlace(RoomEnum::HYDROPONIC_GARDEN, 31, $I);

        $this->whenDayChangesForBananaTree($bananaTree);

        $this->thenBananaTreeShouldBeAdult($bananaTree, $I);

        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $bananaTree->getPlace()->getName(),
                'log' => PlantLogEnum::PLANT_MATURITY,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $this->thenThereIsABananaInRoom($bananaTree->getPlace(), $I);
    }

    public function testDiseasedPlantDayChange(FunctionalTester $I)
    {
        $bananaTree = $this->givenAMatureBananaTreeInPlace(RoomEnum::HYDROPONIC_GARDEN, $I);
        $this->createStatusOn(EquipmentStatusEnum::PLANT_DISEASED, $bananaTree);

        $this->whenDayChangesForBananaTree($bananaTree);

        $this->thenTheresNoBananaInRoom($bananaTree->getPlace(), $I);
    }

    public function testThirstyPlantDayChange(FunctionalTester $I)
    {
        $bananaTree = $this->givenAMatureBananaTreeInPlace(RoomEnum::HYDROPONIC_GARDEN, $I);
        $this->createStatusOn(EquipmentStatusEnum::PLANT_THIRSTY, $bananaTree);

        $this->whenDayChangesForBananaTree($bananaTree);

        $this->thenTheresNoBananaInRoom($bananaTree->getPlace(), $I);
        // then plant grew thirstier
        $I->assertFalse($bananaTree->hasStatus(EquipmentStatusEnum::PLANT_THIRSTY));
        $I->assertTrue($bananaTree->hasStatus(EquipmentStatusEnum::PLANT_DRY));
    }

    public function testDryPlantDayChange(FunctionalTester $I)
    {
        $bananaTree = $this->givenAMatureBananaTreeInPlace(RoomEnum::HYDROPONIC_GARDEN, $I);
        $this->createStatusOn(EquipmentStatusEnum::PLANT_DRY, $bananaTree);

        $this->whenDayChangesForBananaTree($bananaTree);

        $this->thenTheresNoBananaInRoom($bananaTree->getPlace(), $I);
        // then plant has died
        $I->assertTrue($bananaTree->getPlace()->hasEquipmentByName(ItemEnum::HYDROPOT));
    }

    public function testHealthyPlantDayChange(FunctionalTester $I)
    {
        $bananaTree = $this->givenAMatureBananaTreeInPlace(RoomEnum::HYDROPONIC_GARDEN, $I);

        $this->whenDayChangesForBananaTree($bananaTree);

        $this->thenThereIsABananaInRoom($bananaTree->getPlace(), $I);
        // plant grew thirsty
        $I->assertTrue($bananaTree->hasStatus(EquipmentStatusEnum::PLANT_THIRSTY));
    }

    public function shouldMakePlantsGrowTwiceFasterInGardenWithHydroponicIncubatorProject(FunctionalTester $I): void
    {
        $this->givenThereIsAGardenInDaedalus($I);
        $bananaTree = $this->givenABananaTreeWithMaturationCycleInPlace(RoomEnum::HYDROPONIC_GARDEN, 1, $I);
        $this->givenHydroponicIncubatorProjectIsFinished($I);

        $this->whenCycleChangesForBananaTree($bananaTree);

        $this->thenBananaTreeShouldBe(3, $bananaTree, $I);
    }

    public function shouldNotMakePlantsGrowTwiceFasterOutsideOfGardenWithHydroponicIncubatorProject(FunctionalTester $I): void
    {
        $this->givenThereIsAGardenInDaedalus($I);
        $bananaTree = $this->givenABananaTreeWithMaturationCycleInPlace(RoomEnum::LABORATORY, 1, $I);
        $this->givenHydroponicIncubatorProjectIsFinished($I);

        $this->whenCycleChangesForBananaTree($bananaTree);

        $this->thenBananaTreeShouldBe(2, $bananaTree, $I);
    }

    public function testPlantCycleChangeDisease(FunctionalTester $I)
    {
        $bananaTree = $this->givenAMatureBananaTreeInPlace(RoomEnum::HYDROPONIC_GARDEN, $I);

        $this->daedalus->getGameConfig()->getDifficultyConfig()->setPlantDiseaseRate(100);

        $this->whenCycleChangesForBananaTree($bananaTree);

        // plant grew sick
        $I->assertTrue($bananaTree->hasStatus(EquipmentStatusEnum::PLANT_DISEASED));
    }

    public function shouldNotMakePlantSickIfInGardenWithNanoLadybugs(FunctionalTester $I)
    {
        $bananaTree = $this->givenAMatureBananaTreeInPlace(RoomEnum::HYDROPONIC_GARDEN, $I);

        $this->daedalus->getGameConfig()->getDifficultyConfig()->setPlantDiseaseRate(100);

        $this->givenNanoLadybugsProjectIsFinished($I);

        $this->whenCycleChangesForBananaTree($bananaTree);

        // plant did not grow sick
        $I->assertFalse($bananaTree->hasStatus(EquipmentStatusEnum::PLANT_DISEASED));
    }

    public function shouldMakePlantSickOutsideOfGardenWithNanoLadybugs(FunctionalTester $I)
    {
        $bananaTree = $this->givenAMatureBananaTreeInPlace(RoomEnum::LABORATORY, $I);

        $this->daedalus->getGameConfig()->getDifficultyConfig()->setPlantDiseaseRate(100);

        $this->givenNanoLadybugsProjectIsFinished($I);

        $this->whenCycleChangesForBananaTree($bananaTree);

        // plant grew sick
        $I->assertTrue($bananaTree->hasStatus(EquipmentStatusEnum::PLANT_DISEASED));
    }

    // Heatlamps and halloween jumpkin production are tested in PlantCycleHandlerTest

    private function givenThereIsAGardenInDaedalus(FunctionalTester $I): Place
    {
        return $this->createExtraPlace(
            placeName: RoomEnum::HYDROPONIC_GARDEN,
            I: $I,
            daedalus: $this->daedalus
        );
    }

    private function givenABananaTreeWithMaturationCycleInPlace(string $placeName, int $age, FunctionalTester $I): GameItem
    {
        $place = $this->daedalus->getPlaceByName($placeName);
        if ($place === null) {
            $this->createExtraPlace(
                placeName: $placeName,
                I: $I,
                daedalus: $this->daedalus
            );
        }

        $bananaTree = $this->equipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime()
        );

        $maturationStatus = $bananaTree->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PLANT_YOUNG);
        $maturationStatus->setCharge($age);
        $maturationStatus->setMaxCharge(32);

        return $bananaTree;
    }

    private function givenAMatureBananaTreeInPlace(string $placeName, FunctionalTester $I): GameItem
    {
        $place = $this->daedalus->getPlaceByName($placeName);
        if ($place === null) {
            $this->createExtraPlace(
                placeName: $placeName,
                I: $I,
                daedalus: $this->daedalus
            );
        }

        $bananaTree = $this->equipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime()
        );

        $maturationStatus = $bananaTree->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PLANT_YOUNG);
        $bananaTree->removeStatus($maturationStatus);

        return $bananaTree;
    }

    private function givenHydroponicIncubatorProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::HYDROPONIC_INCUBATOR),
            author: $this->chun,
            I: $I
        );
    }

    private function givenNanoLadybugsProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PARASITE_ELIM),
            author: $this->chun,
            I: $I
        );
    }

    private function whenCycleChangesForBananaTree(GameItem $bananaTree): void
    {
        $cycleEvent = new EquipmentCycleEvent($bananaTree, $this->daedalus, [EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);
    }

    private function whenDayChangesForBananaTree(GameItem $bananaTree): void
    {
        $cycleEvent = new EquipmentCycleEvent($bananaTree, $this->daedalus, [EventEnum::NEW_DAY, EventEnum::NEW_CYCLE], new \DateTime());
        $this->eventService->callEvent($cycleEvent, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);
    }

    private function thenBananaTreeShouldBe(int $age, GameItem $bananaTree, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $age,
            actual: $bananaTree->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PLANT_YOUNG)->getCharge()
        );
    }

    private function thenBananaTreeShouldBeAdult(GameItem $bananaTree, FunctionalTester $I): void
    {
        $I->assertFalse($bananaTree->isYoungPlant());
    }

    private function thenTheresNoBananaInRoom(Place $place, FunctionalTester $I): void
    {
        $I->dontSeeInRepository(
            RoomLog::class,
            [
                'place' => $place->getName(),
                'log' => PlantLogEnum::PLANT_NEW_FRUIT,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->assertFalse($place->hasEquipmentByName(GameFruitEnum::BANANA));
    }

    private function thenThereIsABananaInRoom(Place $place, FunctionalTester $I): void
    {
        $I->SeeInRepository(
            RoomLog::class,
            [
                'place' => $place->getName(),
                'log' => PlantLogEnum::PLANT_NEW_FRUIT,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->assertTrue($place->hasEquipmentByName(GameFruitEnum::BANANA));
    }
}
