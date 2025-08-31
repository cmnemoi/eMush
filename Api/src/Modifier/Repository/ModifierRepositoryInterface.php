<?php

declare(strict_types=1);

namespace Mush\Modifier\Repository;

use Mush\Modifier\Entity\GameModifier;

interface ModifierRepositoryInterface
{
    public function save(GameModifier $gameModifier): void;

    public function delete(GameModifier $gameModifier): void;

    public function wrapInTransaction(callable $callable): void;
}
