<?php

namespace Mush\Game\Service;

use Mush\Game\Event\AbstractGameEvent;

interface EventServiceInterface
{
    public function callEvent(AbstractGameEvent $event, string $name, AbstractGameEvent $caller = null): void;
}
