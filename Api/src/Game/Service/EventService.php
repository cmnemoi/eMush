<?php

namespace Mush\Game\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class EventService implements EventServiceInterface
{

    private array $tree = [];
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
         $this->eventDispatcher = $eventDispatcher;
    }

    public function callEvent(Event $eventParameters, string $name, bool $priority = false) : void {
        if (empty($this->tree)) {
            $this->tree = [[
                'parameters' => $eventParameters,
                'name' => $name,
                'next' => [],
            ]];

            while (!empty($this->tree)) {
                codecept_debug($this->tree[0]['name']);
                $this->eventDispatcher->dispatch($this->tree[0]['parameters'], $this->tree[0]['name']);

                if (empty($this->tree[0]['next'])) {
                    $this->tree = array_slice($this->tree, 1);
                } else {
                    $this->tree = array_merge($this->tree[0]['next'], array_slice($this->tree, 1));
                }
            }
        } else if ($priority) {
            $this->eventDispatcher->dispatch($eventParameters, $name);
        } else {
            $this->tree[0]['next'] = array_merge($this->tree[0]['next'], [[
                'parameters' => $eventParameters,
                'name' => $name,
                'next' => [],
            ]]);
        }
    }
}
