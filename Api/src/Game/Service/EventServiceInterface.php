<?php

namespace Mush\Game\Service;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\AbstractGameEvent;

interface EventServiceInterface
{
    public function callEvent(AbstractGameEvent $event, string $name, AbstractGameEvent $caller = null): EventChain;

    public function computeEventModifications(AbstractGameEvent $event, string $name): ?AbstractGameEvent;

    public function eventCancelReason(AbstractGameEvent $event, string $name): ?string;
}
