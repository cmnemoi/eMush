<?php

namespace Mush\Action\ActionResult;

use Mush\RoomLog\Entity\Target;

abstract class ActionResult
{
    private ?string $log;
    private ?string $visibility;
    private ?Target $target;

    public function __construct(
        ?string $log = null,
        ?string $visibility = null,
        ?Target $target = null
    ) {
        $this->log = $log;
        $this->visibility = $visibility;
        $this->target = $target;
    }

    public function getLog(): ?string
    {
        return $this->log;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function getTarget(): ?Target
    {
        return $this->target;
    }
}
