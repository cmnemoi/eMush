<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Communications\TestDoubles\Repository;

use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Repository\XylophRepositoryInterface;

final class InMemoryXylophRepository implements XylophRepositoryInterface
{
    /** @var XylophEntry[] */
    private array $xylophEntries = [];

    public function deleteAllByDaedalusId(int $daedalusId): void
    {
        foreach ($this->xylophEntries as $key => $xylophEntry) {
            if ($xylophEntry->getDaedalusId() === $daedalusId) {
                unset($this->xylophEntries[$key]);
            }
        }
    }

    public function findAllByDaedalusId(int $daedalusId): array
    {
        $result = [];
        foreach ($this->xylophEntries as $xylophEntry) {
            if ($xylophEntry->getDaedalusId() === $daedalusId) {
                $result[] = $xylophEntry;
            }
        }

        return $result;
    }

    public function findAllUndecodedByDaedalusId(int $daedalusId): array
    {
        $result = [];
        foreach ($this->xylophEntries as $xylophEntry) {
            if ($xylophEntry->getDaedalusId() === $daedalusId && !$xylophEntry->isDecoded()) {
                $result[] = $xylophEntry;
            }
        }

        return $result;
    }

    public function findByDaedalusIdAndNameOrThrow(int $daedalusId, string $name): XylophEntry
    {
        foreach ($this->xylophEntries as $xylophEntry) {
            if ($xylophEntry->getDaedalusId() === $daedalusId && $xylophEntry->getName() === $name) {
                return $xylophEntry;
            }
        }

        throw new \RuntimeException("XylophEntry {$name} not found for daedalus {$daedalusId}");
    }

    public function areAllXylophDatabasesDecoded(int $daedalusId): bool
    {
        foreach ($this->xylophEntries as $xylophEntry) {
            if ($xylophEntry->getDaedalusId() === $daedalusId && !$xylophEntry->isDecoded()) {
                return false;
            }
        }

        return true;
    }

    public function save(XylophEntry $xylophEntry): void
    {
        $this->xylophEntries[$xylophEntry->getDaedalusId() . '-' . $xylophEntry->getName()->toString()] = $xylophEntry;
    }
}
