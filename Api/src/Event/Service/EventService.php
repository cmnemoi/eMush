<?php

namespace Mush\Event\Service;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventService implements EventServiceInterface
{

    private array $tree = [];
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
         $this->eventDispatcher = $eventDispatcher;
    }

    public function callEvent(Event $eventParameters, string $name) : void {
        if (!$this->tree) {
            $this->tree = [[
                'parameters' => $eventParameters,
                'name' => $name,
                'next' => [],
            ]];

            while (!empty($this->tree)) {
                $this->eventDispatcher->dispatch($this->tree[0]['parameters'], $this->tree[0]['name']);

                if (empty($this->tree[0]['next'])) {
                    $this->tree = array_slice($this->tree, 1);
                } else {
                    $this->tree = array_merge($this->tree[0]['next'], array_slice($this->tree, 1));
                }
            }
        } else {
            $this->tree[0]['next'] = array_merge($this->tree[0]['next'], [[
                'parameters' => $eventParameters,
                'name' => $name,
                'next' => [],
            ]]);
        }
    }
}
