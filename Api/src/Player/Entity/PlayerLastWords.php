<?php

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class PlayerLastWords
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\OneToOne (targetEntity="Mush\Player\Entity\Player")
     */
    private Player $player;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $message;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): PlayerLastWords
    {
        $this->player = $player;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): PlayerLastWords
    {
        $this->message = $message;

        return $this;
    }
}
