<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Attempt extends ChargeStatus
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $action;

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }
}
