<?php

declare(strict_types=1);

namespace Mush\Player\ValueObject;

interface PlayerHighlightTargetInterface
{
    public function getLogKey(): string;

    public function getLogName(): string;
}
