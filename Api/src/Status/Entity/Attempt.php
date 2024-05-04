<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Enum\ActionEnum;

#[ORM\Entity]
class Attempt extends ChargeStatus
{
    #[ORM\Column(type: 'string', nullable: false, enumType: ActionEnum::class)]
    private ActionEnum $action;

    public function getAction(): ActionEnum
    {
        return $this->action;
    }

    public function setAction(ActionEnum $action): static
    {
        $this->action = $action;

        return $this;
    }
}
