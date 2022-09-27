<?php

namespace Mush\Game\Service;

use Symfony\Contracts\EventDispatcher\Event;

interface EventServiceInterface
{

    public function callEvent(Event $eventParameters, string $name) : void;

}