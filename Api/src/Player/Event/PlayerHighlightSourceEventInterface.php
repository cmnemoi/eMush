<?php

declare(strict_types=1);

namespace Mush\Player\Event;

use Mush\Player\ValueObject\PlayerHighlightTargetInterface;

interface PlayerHighlightSourceEventInterface
{
    public function getAuthorOrThrow(): PlayerHighlightTargetInterface;

    public function getHighlightName(): string;

    public function getHighlightResult(): string;

    public function getHighlightTarget(): PlayerHighlightTargetInterface;

    public function hasHighlightTarget(): bool;

    public function recordHighlights(): void;
}
