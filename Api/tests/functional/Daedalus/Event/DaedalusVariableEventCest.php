<?php

namespace functional\Daedalus\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierCondition;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DaedalusVariableEventCest
{
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testChangeOxygenWithTanks(FunctionalTester $I)
    {
        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['daedalusConfig' => $daedalusConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'oxygen' => 32]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $event = new DaedalusModifierEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            -2,
            EventEnum::NEW_CYCLE,
            new DateTime()
        );
        $this->eventDispatcher->dispatch($event, AbstractQuantityEvent::CHANGE_VARIABLE);

        $I->assertEquals(30, $daedalus->getOxygen());

        //add an oxygen tank
        $modifierCondition = new ModifierCondition(ModifierConditionEnum::REASON);
        $modifierCondition->setCondition(EventEnum::NEW_CYCLE);
        $I->haveInRepository($modifierCondition);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setGameConfig($gameConfig)
            ->setTarget(DaedalusVariableEnum::OXYGEN)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope(AbstractQuantityEvent::CHANGE_VARIABLE)
            ->addModifierCondition($modifierCondition)
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new Modifier($daedalus, $modifierConfig);
        $I->haveInRepository($modifier);

        $event = new DaedalusModifierEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            -2,
            'other_reason',
            new DateTime()
        );
        $this->eventDispatcher->dispatch($event, AbstractQuantityEvent::CHANGE_VARIABLE);

        $I->assertEquals(28, $daedalus->getOxygen());

        $event = new DaedalusModifierEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            -2,
            EventEnum::NEW_CYCLE,
            new DateTime()
        );
        $this->eventDispatcher->dispatch($event, AbstractQuantityEvent::CHANGE_VARIABLE);

        $I->assertEquals(27, $daedalus->getOxygen());
    }
}
