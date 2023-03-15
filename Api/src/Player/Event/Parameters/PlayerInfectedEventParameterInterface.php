<?php

namespace Mush\Player\Event\Parameters;

use Mush\Player\Entity\Player;

interface PlayerInfectedEventParameterInterface
{
    public function getInfectionCause(): string;

    public function getAuthorCharacter(): Player;

    public function getTargetCharacter(): Player;
}
