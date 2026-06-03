<?php

declare(strict_types=1);

namespace Mush\Player\Event\Parameters;

use Mush\Player\Entity\Player;

interface PlayerInfectedEventParameterInterface
{
    public function getInfectionCause(): string;

    public function getAuthorCharacter(): Player;

    public function getTargetCharacter(): Player;
}
