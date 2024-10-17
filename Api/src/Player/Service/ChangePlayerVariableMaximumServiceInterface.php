<?php

declare(strict_types=1);

namespace Mush\Player\Service;

use Mush\Player\Entity\Player;

interface ChangePlayerVariableMaximumServiceInterface
{
    public function execute(
        Player $player,
        string $variableName,
        int $delta,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ): void;
}
