<?php

declare(strict_types=1);

namespace Mush\Status\Entity;

interface VisibleStatusHolderInterface
{
    public function getLogKey(): string;

    public function getLogName(): string;
}
