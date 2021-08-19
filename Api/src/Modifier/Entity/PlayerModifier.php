<?php

namespace Mush\Modifier\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Player\Entity\Player;

/**
 * Class PlayerModifier.
 *
 * @ORM\Entity
 */
class PlayerModifier extends Modifier
{
    /**
     * @ORM\ManyToOne (targetEntity="Mush\Player\Entity\Player", inversedBy="modifiers")
     */
    private Player $player;

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): PlayerModifier
    {
        $this->player = $player;

        $player->addModifier($this);

        return $this;
    }
}
