<?php

namespace Mush\RoomLog\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class RoomLogTableContent
{
    private array $header = [];
    private Collection $entries;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function setHeader(array $newHeader): static
    {
        $this->header = ['header' => $newHeader];

        return $this;
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function addOneEntry(array $newEntry): static
    {
        $entry = ['entry' => $newEntry];
        $this->entries->add($entry);

        return $this;
    }

    public function getEntries(): ArrayCollection
    {
        return new ArrayCollection($this->entries->toArray());
    }

    public function toArray(): array
    {
        $table = [];
        $table[] = $this->getHeader();
        foreach ($this->getEntries() as $entry) {
            $table[] = $entry;
        }

        return $table;
    }
}
