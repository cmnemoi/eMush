<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\GameConfig;
use Mush\Player\Entity\Player;

/**
 * Class StatusEffect
 * @package Mush\Entity
 *
 * @ORM\Entity()
 */
class Affliction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Player\Entity\Player", inversedBy="afflictions")
     */
    private Player $player;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Status\Entity\AfflictionConfig")
     */
    private AfflictionConfig $afflictionConfig;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): Affliction
    {
        $this->player = $player;
        return $this;
    }

    public function getAfflictionConfig(): AfflictionConfig
    {
        return $this->afflictionConfig;
    }

    public function setAfflictionConfig(AfflictionConfig $afflictionConfig): Affliction
    {
        $this->afflictionConfig = $afflictionConfig;
        return $this;
    }
}
