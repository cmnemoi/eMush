<?php

namespace Mush\Game\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class EventService implements EventServiceInterface
{

    private array $tree = [];
    private bool $cascade = false;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
         $this->eventDispatcher = $eventDispatcher;
    }

    public function callEvent(Event $eventParameters, string $name, bool $cascade = false) : void {
        if ($this->cascade) {
            codecept_debug($name);
            $this->eventDispatcher->dispatch($eventParameters, $name);
        }

        if ($cascade) {
            $this->cascade = true;
            codecept_debug($name);
            $this->eventDispatcher->dispatch($eventParameters, $name);
            $this->cascade = false;
        } else if (empty($this->tree)) {
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
        } else {
            $this->tree[0]['next'] = array_merge($this->tree[0]['next'], [[
                'parameters' => $eventParameters,
                'name' => $name,
                'next' => [],
            ]]);
        }
    }
}
