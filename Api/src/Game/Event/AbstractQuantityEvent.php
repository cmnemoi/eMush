<?php

namespace Mush\Game\Event;

interface AbstractQuantityEvent
{
    public function getQuantity(): int;

    public function getReason(): string;
}
