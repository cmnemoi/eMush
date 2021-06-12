<?php

namespace Mush\RoomLog\Service\Parameter;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractHandler
{
    /** @var Event */
    protected $data;

    public function bindData(Event $event): self
    {
        $this->data = $event;
        return $this;
    }
}