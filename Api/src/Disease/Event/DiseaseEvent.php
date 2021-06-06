<?php

namespace Mush\Disease\Event;

use Mush\Disease\Entity\DiseaseConfig;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class DiseaseEvent extends Event
{
    public const NEW_DISEASE = 'disaese.new';
    public const CURE_DISEASE = 'disaese.cure';

    private Player $player;

    private DiseaseConfig $diseaseConfig;

    public function __construct(Player $player, DiseaseConfig $diseaseConfig)
    {
        $this->player = $player;
        $this->diseaseConfig = $diseaseConfig;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getDiseaseConfig(): DiseaseConfig
    {
        return $this->diseaseConfig;
    }
}
