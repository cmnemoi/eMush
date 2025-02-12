<?php

declare(strict_types=1);

namespace Mush\Communications\Entity;

class NeronVersion
{
    private int $major;

    private int $minor;

    private int $daedalusId;

    public function __construct(int $major, int $minor, int $daedalusId)
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->daedalusId = $daedalusId;
    }

    public function getMajor(): int
    {
        return $this->major;
    }

    public function getDaedalusId(): int
    {
        return $this->daedalusId;
    }

    public function increment(int $minorIncrement): void
    {
        if ($this->minor + $minorIncrement >= 100) {
            ++$this->major;
        }

        $this->minor = ($this->minor + $minorIncrement) % 100;
    }

    public function toString(): string
    {
        $minor = str_pad((string) $this->minor, 2, '0', STR_PAD_LEFT);

        return "{$this->major}.{$minor}";
    }
}
