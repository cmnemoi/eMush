<?php

namespace Mush\RoomLog\Service\Parameter;

class HandlersFactory
{
    /** @var array */
    private $handlers = null;

    public function create(): array
    {
        if (!isset($this->handlers)) {
            $this->handlers = [
                new Character,
                new DiseaseConfig,
            ];
        }
        return $this->handlers;
    }
}