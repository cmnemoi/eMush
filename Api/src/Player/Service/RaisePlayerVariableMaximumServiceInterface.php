<?php

declare(strict_types=1);

namespace Mush\Player\Service;

use Mush\Player\Entity\Player;

interface RaisePlayerVariableMaximumServiceInterface
{
    public function execute(
        Player $player,
        string $variableName,
        int $delta,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ): void;
}
