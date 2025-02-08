<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

use Mush\Communications\ValueObject\SignalStrength;

class LinkWithSol
{
    private int $id;

    private SignalStrength $strength;

    private bool $isEstablished;

    private int $daedalusId;

    public function __construct(int $strength, bool $isEstablished, int $daedalusId)
    {
        $this->strength = new SignalStrength($strength);
        $this->isEstablished = $isEstablished;
        $this->daedalusId = $daedalusId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStrength(): int
    {
        return $this->strength->value;
    }

    public function getDaedalusId(): int
    {
        return $this->daedalusId;
    }

    public function isEstablished(): bool
    {
        return $this->isEstablished;
    }

    public function increaseStrength(int $strengthIncrease): void
    {
        $this->strength = $this->strength->increase($strengthIncrease);
    }

    public function markAsEstablished(): void
    {
        $this->isEstablished = true;
    }
}
