<?php

declare(strict_types=1);

namespace Mush\Hunter\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Skill\Enum\SkillEnum;

final class StrateguruWorkedEvent extends AbstractGameEvent
{
    private PlayerCollection $strateguruPlayers;

    public function __construct(
        private Daedalus $daedalus,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);

        $this->strateguruPlayers = $this->daedalus->getAlivePlayers()->getPlayersWithSkill(SkillEnum::STRATEGURU);
    }

    public function getStrateguruPlayers(): PlayerCollection
    {
        return $this->strateguruPlayers;
    }
}
