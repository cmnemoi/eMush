<?php

namespace Mush\Game\Service;

use Symfony\Contracts\EventDispatcher\Event;

interface eventDispatcherInterface
{

    public function dispatch(Event $eventParameters, string $name) : void;

}