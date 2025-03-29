<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\TestDoubles\Service;

use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\EventServiceInterface;

final class FakeEventService implements EventServiceInterface
{
    public function callEvent(AbstractGameEvent $event, string $name, ?AbstractGameEvent $caller = null): EventChain
    {
        if ($event instanceof DaedalusVariableEvent) {
            $daedalusVariable = $event->getVariable();
            $daedalusVariable->changeValue($event->getRoundedQuantity());
        }

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
