<?php

declare(strict_types=1);

namespace Mush\MetaGame\Command;

final readonly class MarkDaedalusAsCheaterCommand
{
    public function __construct(public int $closedDaedalusId) {}
}
