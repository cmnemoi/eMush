<?php

namespace functional\Daedalus\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;

class DaedalusVariableEventCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testChangeOxygenWithTanks(FunctionalTester $I): void
    {
        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['daedalusConfig' => $daedalusConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'oxygen' => 32]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $event = new DaedalusVariableEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            -2,
            EventEnum::NEW_CYCLE,
            new DateTime()
        );
        $this->eventService->callEvent($event, AbstractQuantityEvent::CHANGE_VARIABLE);

        $I->assertEquals(30, $daedalus->getOxygen());

        $modifierConfig = new ModifierConfig(
            'a random modifier config',
            ModifierReachEnum::DAEDALUS,
            1,
            ModifierModeEnum::ADDITIVE,
            DaedalusVariableEnum::OXYGEN
        );
        $modifierConfig
            ->addTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE, [EventEnum::NEW_CYCLE]);
        $I->haveInRepository($modifierConfig);

        $modifier = new Modifier($daedalus, $modifierConfig);
        $I->haveInRepository($modifier);

        $event = new DaedalusVariableEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            -2,
            'a random reason',
            new DateTime()
        );
        $this->eventService->callEvent($event, AbstractQuantityEvent::CHANGE_VARIABLE);

        $I->assertEquals(28, $daedalus->getOxygen());

        $event = new DaedalusVariableEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            -2,
            EventEnum::NEW_CYCLE,
            new DateTime()
        );
        $this->eventService->callEvent($event, AbstractQuantityEvent::CHANGE_VARIABLE);

        $I->assertEquals(27, $daedalus->getOxygen());
    }
}
