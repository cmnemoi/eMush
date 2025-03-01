<?php

declare(strict_types=1);

namespace Mush\Communications\Repository;

use Mush\Communications\Entity\XylophEntry;

interface XylophRepositoryInterface
{
    public function deleteAllByDaedalusId(int $daedalusId): void;

    /**
     * @return XylophEntry[]
     */
    public function findAllByDaedalusId(int $daedalusId): array;

    /**
     * @return XylophEntry[]
     */
    public function findAllUndecodedByDaedalusId(int $daedalusId): array;

    public function findByDaedalusIdAndNameOrThrow(int $daedalusId, string $name): XylophEntry;

    public function areAllXylophDatabasesDecoded(int $daedalusId): bool;

    public function save(XylophEntry $xylophEntry): void;
}
