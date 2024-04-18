<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\Event;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Event\SymptomEvent;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SymptomEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function testDirtinessSymptomMakesPlayerDirtyEventIfPlayerHasApron(FunctionalTester $I): void
    {
        // given Chun has an apron
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::STAINPROOF_APRON,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime()
        );

        // when I trigger dirtiness symptom event for Chun
        $symptomEvent = new SymptomEvent(
            player: $this->chun,
            symptomName: SymptomEnum::DIRTINESS,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($symptomEvent, SymptomEvent::TRIGGER_SYMPTOM);

        // then Chun should be dirty
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::DIRTY));
    }

    public function testVomitingSymptomMakesPlayerDirtyEventIfPlayerHasApron(FunctionalTester $I): void
    {
        // given Chun has an apron
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::STAINPROOF_APRON,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime()
        );

        // when I trigger vomiting symptom event for Chun
        $symptomEvent = new SymptomEvent(
            player: $this->chun,
            symptomName: SymptomEnum::VOMITING,
            tags: [],
            time: new \DateTime()
        );
        $this->eventService->callEvent($symptomEvent, SymptomEvent::TRIGGER_SYMPTOM);

        // then Chun should be dirty
        $I->assertTrue($this->chun->hasStatus(PlayerStatusEnum::DIRTY));
    }
}
