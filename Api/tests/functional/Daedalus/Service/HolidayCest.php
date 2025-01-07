<?php

namespace Mush\Tests\functional\Daedalus\Service;

use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusService;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\HolidayEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Service\PlaceServiceInterface;
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
        $this->givenHolidayIsAnniversary();
        $this->whenShipIsFull();
        $this->thenChunAndKuanTiShouldHaveGifts($I);
    }

    public function anniversaryCreatesNeronAnnouncementWhenShipIsFull(FunctionalTester $I): void
    {
        $this->givenHolidayIsAnniversary();
        $this->whenShipIsFull();
        $this->thenNeronAnnouncesAnniversary($I);
    }

    public function halloweenCreatesJumpkinInGardenWhenShipStarts(FunctionalTester $I): void
    {
        $this->givenHolidayIsHalloween();
        $this->whenGardenIsCreated($I);
        $this->thenJumpkinIsInGarden($I);
    }

    public function halloweenCreatesNeronAnnouncementWhenShipIsFull(FunctionalTester $I): void
    {
        $this->givenHolidayIsHalloween();
        $this->whenShipIsFull();
        $this->thenNeronAnnouncesHalloween($I);
    }

    private function givenHolidayIsAnniversary(): void
    {
        $this->daedalus->getDaedalusConfig()->setHoliday(HolidayEnum::ANNIVERSARY);
    }

    private function givenHolidayIsHalloween(): void
    {
        $this->daedalus->getDaedalusConfig()->setHoliday(HolidayEnum::HALLOWEEN);
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
}
