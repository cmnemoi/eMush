<?php

namespace Mush\Player\Event\Parameters;

use Mush\Player\Entity\Player;

class PlayerInfectedEventParameter implements PlayerInfectedEventParameterInterface
{
    private string $infectionCause;
    private Player $authorCharacter;
    private Player $targetCharacter;

    public function __construct(string $infectionCause, Player $authorCharacter, Player $targetCharacter)
    {
        $this->infectionCause = $infectionCause;
        $this->authorCharacter = $authorCharacter;
        $this->targetCharacter = $targetCharacter;
    }

    public function getInfectionCause(): string
    {
        return $this->infectionCause;
    }

    public function getAuthorCharacter(): Player
    {
        return $this->authorCharacter;
    }

    public function getTargetCharacter(): Player
    {
        return $this->targetCharacter;
    }
}
