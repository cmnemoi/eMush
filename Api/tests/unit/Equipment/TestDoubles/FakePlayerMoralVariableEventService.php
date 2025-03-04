<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Equipment\TestDoubles;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\EventServiceInterface;

/**
 * Class to fake PlayerVariableEvent handling.
 * For this test we are just interested in the morale point increment (we trust everything related to event handling is tested outside)
 * so we basically hardcoding it.
 */
final class FakePlayerMoralVariableEventService implements EventServiceInterface
{
    public function callEvent(AbstractGameEvent $event, string $name, ?AbstractGameEvent $caller = null): EventChain
    {
        $player = $event->getPlayer();
        $player->setMoralPoint($player->getMoralPoint() + 2);

        return new EventChain();
    }

    public function computeEventModifications(AbstractGameEvent $event, string $name): ?AbstractGameEvent
    {
        return null;
    }

    public function eventCancelReason(AbstractGameEvent $event, string $name): ?string
    {
        return null;
    }
}
