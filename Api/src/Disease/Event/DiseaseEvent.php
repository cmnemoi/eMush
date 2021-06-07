<?php

namespace Mush\Disease\Event;

use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class DiseaseEvent extends Event
{
    public const NEW_DISEASE = 'disaese.new';
    public const APPEAR_DISEASE = 'disaese.appear';
    public const CURE_DISEASE = 'disaese.cure';

    private PlayerDisease $playerDisease;
    private \DateTime $time;

    public function __construct(PlayerDisease $playerDisease, \DateTime $time)
    {
        $this->playerDisease = $playerDisease;
        $this->time = $time;
    }

    public function getPlayer(): Player
    {
        return $this->playerDisease->getPlayer();
    }

    public function getDiseaseConfig(): DiseaseConfig
    {
        return $this->playerDisease->getDiseaseConfig();
    }

    public function getPlayerDisease(): PlayerDisease
    {
        return $this->playerDisease;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }
}
