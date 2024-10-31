<?php

declare(strict_types=1);

namespace Mush\Player\Repository;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;

interface PlayerRepositoryInterface
{
    public function save(Player $player): void;

    public function delete(Player $player): void;

    public function startTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;

    public function lockAndRefresh(Player $player, int $mode): void;

    public function getAll(): array;

    public function findById(int $id): ?Player;

    public function findOneByNameAndDaedalus(string $name, Daedalus $daedalus): ?Player;
}
