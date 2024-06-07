<?php

declare(strict_types=1);

namespace Mush\Place\Entity;

interface MoveableEntityInterface
{
    public function getPlace(): Place;
}
