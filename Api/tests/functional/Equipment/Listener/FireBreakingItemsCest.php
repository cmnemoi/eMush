<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventService;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\StatusEventLogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class FireBreakingItemsCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenFireGuaranteesEquipmentDamage();
        $this->givenFireInRoom($I);
    }

    public function shouldFireBreakBreakableItem(FunctionalTester $I): void
    {
        $talkie = $this->givenRoomHasAnItem(ItemEnum::BLASTER);
        $this->whenCyclePasses();
        $this->thenThisItemShouldBeBroken($talkie, $I);
        $this->thenBrokenItemLogIsPrinted($I);
    }

    public function shouldFireDestroyOnBreak(FunctionalTester $I): void
    {
        $this->givenRoomHasAnItem(ItemEnum::POST_IT);
        $this->whenCyclePasses();
        $this->thenRoomShouldHaveEquipmentOfTheFollowingAmount(0, $I);
        $this->thenBrokenItemLogIsNotPrinted($I);
        $this->thenItemDestroyedLogIsPrinted($I);
    }

    public function shouldFireDestroyMultipleShells(FunctionalTester $I): void
    {
        $this->givenRoomHasAnItem(GearItemEnum::INVERTEBRATE_SHELL);
        $this->givenRoomHasAnItem(GearItemEnum::INVERTEBRATE_SHELL);
        $this->givenRoomHasAnItem(GearItemEnum::INVERTEBRATE_SHELL);
        $this->thenRoomShouldHaveEquipmentOfTheFollowingAmount(3, $I);
        $this->whenCyclePasses();
        $this->thenRoomShouldHaveEquipmentOfTheFollowingAmount(0, $I);
        $this->thenBrokenItemLogIsNotPrinted($I);
        $this->thenShellExplosionIsPrinted($I);
    }

    private function givenRoomHasAnItem(string $itemName): GameEquipment
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $itemName,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenFireGuaranteesEquipmentDamage(): void
    {
        $this->daedalus->getGameConfig()->getDifficultyConfig()->setEquipmentFireBreakRate(100);
    }

    private function givenFireInRoom(): void
    {
        $fireStatus = $this->statusService->createStatusFromName(
            statusName: StatusEnum::FIRE,
            holder: $this->player->getPlace(),
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenCyclePasses(): void
    {
        $this->eventService->callEvent(
            event: new DaedalusCycleEvent(
                daedalus: $this->daedalus,
                tags: [EventEnum::NEW_CYCLE],
                time: new \DateTime(),
            ),
            name: DaedalusCycleEvent::DAEDALUS_NEW_CYCLE,
        );
    }

    private function thenRoomShouldHaveEquipmentOfTheFollowingAmount(int $expectedCount, FunctionalTester $I): void
    {
        $I->assertCount($expectedCount, $this->player->getPlace()->getEquipments(), "The room should have {$expectedCount} equipment, got {$this->player->getPlace()->getEquipments()->count()}");
    }

    private function thenThisItemShouldBeBroken(GameEquipment $item, FunctionalTester $I): void
    {
        $I->assertTrue($item->hasStatus(EquipmentStatusEnum::BROKEN));
    }

    private function thenBrokenItemLogIsPrinted(FunctionalTester $I): void
    {
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'log' => StatusEventLogEnum::EQUIPMENT_BROKEN,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    private function thenBrokenItemLogIsNotPrinted(FunctionalTester $I): void
    {
        $I->dontSeeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'log' => StatusEventLogEnum::EQUIPMENT_BROKEN,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    private function thenItemDestroyedLogIsPrinted(FunctionalTester $I): void
    {
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'log' => LogEnum::EQUIPMENT_DESTROYED,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    private function thenShellExplosionIsPrinted(FunctionalTester $I): void
    {
        $I->seeInRepository(RoomLog::class, [
            'place' => $this->player->getPlace()->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'log' => LogEnum::INVERTEBRATE_SHELL_EXPLOSION,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
