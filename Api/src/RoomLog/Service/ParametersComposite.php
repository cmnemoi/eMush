<?php

namespace Mush\RoomLog\Service;

use Mush\RoomLog\Service\Parameter\HandlersFactory;
use Mush\RoomLog\Service\Parameter\HandlerInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ParametersComposite
{
    /** @var HandlerInterface[] */
    private $handlers = [];

    public function __construct(HandlersFactory $handlersFactory)
    {
        $this->handlers = $handlersFactory->create();
    }

    public function execute(Event $event): array
    {
        $results = [];
        foreach($this->handlers as $handler) {
            if (!$handler->bindData($event)->canProcess()) {
                continue;
            }
            $results[$handler->getName()] = $handler->execute();
        }
        return $results;
    }
}
