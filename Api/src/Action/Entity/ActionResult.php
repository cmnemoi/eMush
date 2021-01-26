<?php

namespace Mush\Action\ActionResult;

use Mush\RoomLog\Entity\Target;

abstract class ActionResult
{
    private ?Target $target;

    public function __construct(?Target $target = null) {
        $this->target = $target;
    }

    public function setTarget(?Target $target): ActionResult
    {
        $this->target = $target;
        return $this;
    }

    public function getTarget(): ?Target
    {
        return $this->target;
    }
}
