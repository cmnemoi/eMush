<?php

namespace Mush\Player\Event;

use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\Parameters\PlayerInfectedEventParameterInterface;
use Mush\RoomLog\Event\LoggableEventInterface;

class PlayerInfectedEvent extends PlayerVariableEvent implements LoggableEventInterface, VariableEventInterface
{
    private PlayerInfectedEventParameterInterface $infectedParameters;

    public function __construct(
        Player $player,
        string $variableName,
        int $quantity,
        array $tags,
        PlayerInfectedEventParameterInterface $infectedParameters,
        \DateTime $time
    ) {
        $this->infectedParameters = $infectedParameters;
        parent::__construct($player, $variableName, $quantity, $tags, $time);
    }

    public function getLogParameters(): array
    {
        $array = parent::getLogParameters();
        $array['cause'] = $this->infectedParameters->getInfectionCause();
        $array['target_character'] = $this->infectedParameters->getTargetCharacter()->getLogName();
        $array['character'] = $this->infectedParameters->getAuthorCharacter()->getLogName();
        $array['infection_level'] = $this->infectedParameters->getTargetCharacter()->getVariableByName(PlayerVariableEnum::SPORE)->getValue();

        return $array;
    }
}
