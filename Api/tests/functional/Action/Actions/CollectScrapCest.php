<?php

declare(strict_types=1);

namespace functional\Action\Actions;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Action\ActionResult\Success;
use Mush\Action\Actions\CollectScrap;
use Mush\Action\Actions\Land;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;

final class CollectScrapActionCest extends AbstractFunctionalTest
{
    private Action $collectScrapActionConfig;
    private Action $landActionConfig;
    private CollectScrap $collectScrapAction;
    private EventServiceInterface $eventService;
    private GameEquipment $pasiphae;
    private Land $landAction;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraRooms($I, $this->daedalus);

        $this->player1->changePlace($this->daedalus->getPlaceByName(RoomEnum::PASIPHAE));

        $this->collectScrapActionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::COLLECT_SCRAP]);
        $this->landActionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::LAND]);

        $this->collectScrapAction = $I->grabService(CollectScrap::class);
        $this->landAction = $I->grabService(Land::class);

        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $this->pasiphae = new GameEquipment($this->daedalus->getPlaceByName(RoomEnum::PASIPHAE));
        $this->pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($this->pasiphae);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testCollectScrapActionNoScrapToCollect(FunctionalTester $I): void
    {
        $this->collectScrapAction->loadParameters($this->collectScrapActionConfig, $this->player1, $this->pasiphae);

        $I->assertFalse($this->collectScrapAction->isVisible());
    }

    public function testCollectScrapActionSuccess(FunctionalTester $I): void
    {
        // spawn some scrap in space
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->player1->getDaedalus()->getSpace(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );

        $this->collectScrapAction->loadParameters($this->collectScrapActionConfig, $this->player1, $this->pasiphae);
        $I->assertTrue($this->collectScrapAction->isVisible());
        $I->assertNull($this->collectScrapAction->cannotExecuteReason());

        $result = $this->collectScrapAction->execute();

        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->collectScrapActionConfig->getActionCost()
        );

        $I->assertInstanceOf(Success::class, $result);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::SCRAP_COLLECTED,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->assertEquals(
            $this->player1->getDaedalus()->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getInitHull(),
            $this->player1->getDaedalus()->getHull()
        );
        $I->assertEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            $this->player1->getHealthPoint()
        );
    }

    public function testCollectScrapWithAttackingHunters(FunctionalTester $I): void
    {
        // spawn some scrap in space
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::METAL_SCRAPS,
            equipmentHolder: $this->daedalus->getSpace(),
            reasons: ['test'],
            time: new \DateTime(),
            visibility: VisibilityEnum::HIDDEN
        );

        // spawn some hunters
        $hunterEvent = new HunterPoolEvent(
            $this->daedalus,
            ['test'],
            new \DateTime(),
        );
        $this->eventService->callEvent($hunterEvent, HunterPoolEvent::UNPOOL_HUNTERS);

        $I->assertNotEmpty($this->daedalus->getAttackingHunters());

        $this->collectScrapAction->loadParameters($this->collectScrapActionConfig, $this->player1, $this->pasiphae);
        $I->assertTrue($this->collectScrapAction->isVisible());
        $I->assertNull($this->collectScrapAction->cannotExecuteReason());

        $result = $this->collectScrapAction->execute();

        $I->assertEquals(
            expected: $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->collectScrapActionConfig->getActionCost(),
            actual: $this->player1->getActionPoint(),
        );
        $I->assertNotEquals(
            expected: $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            actual: $this->player1->getHealthPoint(),
        );

        $I->assertInstanceOf(Success::class, $result);
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::SCRAP_COLLECTED,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => RoomEnum::SPACE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::SCRAP_COLLECTED,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->assertNotEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            $this->player1->getHealthPoint()
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::PASIPHAE,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::ATTACKED_BY_HUNTER,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testLandingWithScrapCollected(FunctionalTester $I): void
    {
        $this->testCollectScrapActionSuccess($I);

        $alphaBay2 = $this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY_2);
        $I->assertFalse($alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS));

        $this->landAction->loadParameters($this->landActionConfig, $this->player1, $this->pasiphae);
        $this->landAction->execute();

        $I->assertTrue($alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS));
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::PATROL_DISCHARGE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testLandingWithoutScrapCollected($I): void
    {
        $alphaBay2 = $this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY_2);
        $I->assertFalse($alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS));

        $this->landAction->loadParameters($this->landActionConfig, $this->player1, $this->pasiphae);
        $this->landAction->execute();

        $I->assertFalse($alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS));
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => LogEnum::PATROL_DISCHARGE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    private function createExtraRooms(FunctionalTester $I, Daedalus $daedalus): void
    {
        /** @var PlaceConfig $pasiphaeRoomConfig */
        $pasiphaeRoomConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::PASIPHAE]);
        $pasiphaeRoom = new Place();
        $pasiphaeRoom
            ->setName(RoomEnum::PASIPHAE)
            ->setType($pasiphaeRoomConfig->getType())
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($pasiphaeRoom);

        $alphaBay2Config = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::ALPHA_BAY_2]);
        $alphaBay2 = new Place();
        $alphaBay2
            ->setName(RoomEnum::ALPHA_BAY_2)
            ->setType($alphaBay2Config->getType())
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($alphaBay2);

        $I->refreshEntities($daedalus);
    }
}
