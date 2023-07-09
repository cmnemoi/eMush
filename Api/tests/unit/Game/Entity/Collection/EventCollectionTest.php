<?php

namespace Mush\Test\Event\Entity\Collection;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Entity\Collection\EventCollection;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\StatusEnum;
use PHPUnit\Framework\TestCase;

class EventCollectionTest extends TestCase
{
    public function testAddEvent()
    {
        $player = new Player();
        $modifierConfig = new VariableEventModifierConfig('unitTestVariableEventModifier');

        $modifier1 = new GameModifier($player, $modifierConfig);
        $modifier2 = new GameModifier($player, $modifierConfig);
        $modifier3 = new GameModifier(new Daedalus(), $modifierConfig);
        $modifier4 = new GameModifier(new Place(), $modifierConfig);

        $modifierCollection1 = new ModifierCollection([$modifier1, $modifier2]);
        $modifierCollection2 = new ModifierCollection([$modifier3, $modifier4]);

        $event1 = new AbstractGameEvent([], new \DateTime());
        $event1->setPriority(-2);

        $event2 = new AbstractGameEvent([], new \DateTime());
        $event2->setPriority(-1);

        $event3 = new AbstractGameEvent([], new \DateTime());
        $event3->setPriority(-1);

        $event4 = new AbstractGameEvent([], new \DateTime());
        $event4->setPriority(0);

        $event5 = new AbstractGameEvent([], new \DateTime());
        $event5->setPriority(1);

        $eventCollection = new EventCollection([$event4]);

        $eventCollection = $eventCollection->addEvent($event5);

        $this->assertCount(2, $eventCollection);
        $this->assertEquals($event4, $eventCollection->first());
        $this->assertEquals($event5, $eventCollection->next());


        $eventCollection = $eventCollection->addEvent($event1);

        $this->assertCount(3, $eventCollection);
        $this->assertEquals($event1, $eventCollection->first());
        $this->assertEquals($event4, $eventCollection->next());
        $this->assertEquals($event5, $eventCollection->next());
    }
}
