<?php

namespace Mush\Equipment\Criteria;

use Mush\Daedalus\Entity\Daedalus;

class GameEquipmentCriteria
{
    private Daedalus $daedalus;

    private ?bool $breakable = null;

    private ?array $instanceOf = null;

    private ?array $notInstanceOf = null;

    public function __construct(Daedalus $daedalus)
    {
        $this->daedalus = $daedalus;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): self
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function isBreakable(): ?bool
    {
        return $this->breakable;
    }

    public function setBreakable(?bool $breakable): self
    {
        $this->breakable = $breakable;

        return $this;
    }

    public function getInstanceOf(): ?array
    {
        return $this->instanceOf;
    }

    public function setInstanceOf(?array $instanceOf): self
    {
        $this->instanceOf = $instanceOf;

        return $this;
    }

    public function getNotInstanceOf(): ?array
    {
        return $this->notInstanceOf;
    }

    public function setNotInstanceOf(?array $notInstanceOf): self
    {
        $this->notInstanceOf = $notInstanceOf;

        return $this;
    }
}
