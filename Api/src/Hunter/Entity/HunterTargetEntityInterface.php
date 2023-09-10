<?php

declare(strict_types=1);

namespace Mush\Hunter\Entity;

interface HunterTargetEntityInterface
{
    public function isInAPatrolShip(): bool;

    public function isInSpace(): bool;

    public function isInSpaceBattle(): bool;
}
