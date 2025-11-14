<?php

namespace Mush\Tests\functional\Daedalus\Service;

use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\HolidayEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HolidayCest extends AbstractFunctionalTest
{
    private DaedalusService $daedalusService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private EventServiceInterface $eventService;
    private PlaceServiceInterface $placeService;
    private \DateTime $dateTime;
    private Place $garden;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->daedalusService = $I->grabService(DaedalusService::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->placeService = $I->grabService(PlaceServiceInterface::class);

        $this->createExtraPlace(RoomEnum::FRONT_STORAGE, $I, $this->daedalus);
    }

    public function anniversaryGivesGiftsWhenShipIsFull(FunctionalTester $I): void
    {
        $this->givenHolidayIs(HolidayEnum::ANNIVERSARY);
        $this->whenShipIsFull();
        $this->thenChunAndKuanTiShouldHaveGifts($I);
    }

    public function anniversaryCreatesNeronAnnouncementWhenShipIsFull(FunctionalTester $I): void
    {
        $this->givenHolidayIs(HolidayEnum::ANNIVERSARY);
        $this->whenShipIsFull();
        $this->thenNeronAnnouncesAnniversary($I);
    }

    public function halloweenCreatesNeronAnnouncementWhenShipIsFull(FunctionalTester $I): void
    {
        $this->givenHolidayIs(HolidayEnum::HALLOWEEN);
        $this->whenShipIsFull();
        $this->thenNeronAnnouncesHalloween($I);
    }

    public function aprilFoolsCreatesPavlovInLaboratoryWhenShipCreated(FunctionalTester $I): void
    {
        $this->givenHolidayIs(HolidayEnum::APRIL_FOOLS);
        // remove lab created during parent::_before
        $this->daedalus->removePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
        $this->whenLaboratoryIsCreated($I);
        $this->thenPavlovIsInLaboratory($I);
    }

    public function aprilFoolsPavlovCreationLog(FunctionalTester $I): void
    {
        $this->givenHolidayIs(HolidayEnum::APRIL_FOOLS);
        // remove lab created during parent::_before
        $this->daedalus->removePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
        $this->whenLaboratoryIsCreated($I);
        $this->thenPavlovAwakensLogInLaboratory($I);
    }

    public function aprilFoolsCreatesNeronAnnouncementWhenShipIsFull(FunctionalTester $I): void
    {
        $this->givenHolidayIs(HolidayEnum::APRIL_FOOLS);
        $this->whenShipIsFull();
        $this->thenNeronAnnouncesAprilFools($I);
    }

    private function givenHolidayIs(string $holiday): void
    {
        $this->daedalus->getDaedalusConfig()->setHoliday($holiday);
    }

    private function whenGardenIsCreated(FunctionalTester $I): void
    {
        $gardenConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::HYDROPONIC_GARDEN]);

        $this->garden = $this->placeService->createPlace(
            $gardenConfig,
            $this->daedalus,
            [],
            new \DateTime(),
        );
    }

    private function whenLaboratoryIsCreated(FunctionalTester $I): void
    {
        $labConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::LABORATORY]);

        $this->garden = $this->placeService->createPlace(
            $labConfig,
            $this->daedalus,
            [],
            new \DateTime(),
        );
    }

    private function whenShipIsFull(): void
    {
        $this->dateTime = new \DateTime();

        $event = new DaedalusEvent(
            $this->daedalus,
            [],
            $this->dateTime,
        );
        $this->eventService->callEvent($event, DaedalusEvent::FULL_DAEDALUS);
    }

    private function thenChunAndKuanTiShouldHaveGifts(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->hasEquipmentByName(ItemEnum::ANNIVERSARY_GIFT));
        $I->assertTrue($this->kuanTi->hasEquipmentByName(ItemEnum::ANNIVERSARY_GIFT));
    }

    private function thenJumpkinIsInGarden(FunctionalTester $I): void
    {
        $I->assertTrue($this->daedalus->getPlaceByName(RoomEnum::HYDROPONIC_GARDEN)->hasEquipmentByName(GameFruitEnum::JUMPKIN));
    }

    private function thenPavlovIsInLaboratory(FunctionalTester $I): void
    {
        $I->assertTrue($this->daedalus->getPlaceByName(RoomEnum::LABORATORY)->hasEquipmentByName(ItemEnum::PAVLOV));
    }

    private function thenPavlovAwakensLogInLaboratory(FunctionalTester $I): void
    {
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->daedalus->getPlaceByName(RoomEnum::LABORATORY)->getLogName(),
                'log' => LogEnum::AWAKEN_PAVLOV,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    private function thenNeronAnnouncesAnniversary(FunctionalTester $I): void
    {
        $message = $I->grabEntityFromRepository(Message::class, [
            'neron' => $this->daedalus->getNeron(),
            'message' => NeronMessageEnum::ANNIVERSARY_BEGIN,
            'channel' => $this->publicChannel,
            'parent' => null,
            'createdAt' => $this->dateTime,
        ]);

        $I->seeInRepository(Message::class, [
            'channel' => $this->publicChannel,
            'message' => NeronMessageEnum::ANNIVERSARY_BEGIN,
        ]);
    }

    private function thenNeronAnnouncesHalloween(FunctionalTester $I): void
    {
        $message = $I->grabEntityFromRepository(Message::class, [
            'neron' => $this->daedalus->getNeron(),
            'message' => NeronMessageEnum::HALLOWEEN_BEGIN,
            'channel' => $this->publicChannel,
            'parent' => null,
            'createdAt' => $this->dateTime,
        ]);

        $I->seeInRepository(Message::class, [
            'channel' => $this->publicChannel,
            'message' => NeronMessageEnum::HALLOWEEN_BEGIN,
        ]);
    }

    private function thenNeronAnnouncesAprilFools(FunctionalTester $I): void
    {
        $message = $I->grabEntityFromRepository(Message::class, [
            'neron' => $this->daedalus->getNeron(),
            'message' => NeronMessageEnum::APRIL_FOOLS_BEGIN,
            'channel' => $this->publicChannel,
            'parent' => null,
            'createdAt' => $this->dateTime,
        ]);

        $I->seeInRepository(Message::class, [
            'channel' => $this->publicChannel,
            'message' => NeronMessageEnum::APRIL_FOOLS_BEGIN,
        ]);
    }
}
