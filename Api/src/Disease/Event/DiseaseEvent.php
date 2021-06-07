<?php

namespace Mush\Disease\Event;

use Mush\Disease\Entity\DiseaseConfig;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class DiseaseEvent extends Event
{
    public const NEW_DISEASE = 'disaese.new';
    public const APPEAR_DISEASE = 'disaese.appear';
    public const CURE_DISEASE = 'disaese.cure';

    private Player $player;
    private DiseaseConfig $diseaseConfig;
    private \DateTime $time;

    public function __construct(Player $player, DiseaseConfig $diseaseConfig, \DateTime $time)
    {
        $this->player = $player;
        $this->diseaseConfig = $diseaseConfig;
        $this->time = $time;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getDiseaseConfig(): DiseaseConfig
    {
        return $this->diseaseConfig;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }
}
