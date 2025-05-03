<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Daedalus\Service;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DaedalusDeletedEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function shouldDeleteGearProperly(FunctionalTester $I): void
    {
        $gear = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::GRAVITY_SIMULATOR,
            equipmentHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY),
            reasons: [],
            time: new \DateTime(),
        );
        $gearId = $gear->getId();

        $this->eventService->callEvent(
            event: new DaedalusEvent(
                daedalus: $this->daedalus,
                tags: [EndCauseEnum::SUPER_NOVA],
                time: new \DateTime(),
            ),
            name: DaedalusEvent::DELETE_DAEDALUS,
        );

        $I->dontSeeInRepository(GameEquipment::class, [
            'id' => $gearId,
        ]);
    }
}
