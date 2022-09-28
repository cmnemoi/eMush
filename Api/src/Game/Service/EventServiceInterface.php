<?php

namespace Mush\Game\Service;

use Symfony\Contracts\EventDispatcher\Event;

interface EventServiceInterface
{

    public function dispatch(Event $eventParameters, string $name) : void;

}