<?php

namespace Mush\RoomLog\Service\Parameter;

use Symfony\Contracts\EventDispatcher\Event;

interface HandlerInterface
{
    /**
     * @var Event $event
     * @return self
     */
    public function bindData(Event $event): self;

    /**
     * @return bool
     */
    public function canProcess(): bool;

    /**
     * @return string
     */
    public function getName(): string;

    public function execute();
}