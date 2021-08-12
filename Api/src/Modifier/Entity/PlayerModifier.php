<?php

namespace Mush\Modifier\Entity;

use Mush\Player\Entity\Player;

/**
 * Class Modifier.
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

        return $this;
    }
}
