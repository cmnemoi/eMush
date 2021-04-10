<?php

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class DeadPlayerInfo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $message = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $endStatus = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $dayDeath = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $cycleDeath = null;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $likes = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): DeadPlayerInfo
    {
        $this->message = $message;

        return $this;
    }

    public function getDayDeath(): ?int
    {
        return $this->dayDeath;
    }

    /**
     * @return static
     */
    public function setDayDeath(int $dayDeath): DeadPlayerInfo
    {
        $this->dayDeath = $dayDeath;

        return $this;
    }

    public function getCycleDeath(): ?int
    {
        return $this->cycleDeath;
    }

    /**
     * @return static
     */
    public function setCycleDeath(int $cycleDeath): DeadPlayerInfo
    {
        $this->cycleDeath = $cycleDeath;

        return $this;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }

    /**
     * @return static
     */
    public function addlikes(int $change): DeadPlayerInfo
    {
        $this->likes += $change;

        return $this;
    }

    public function getEndStatus(): ?string
    {
        return $this->endStatus;
    }

    /**
     * @return static
     */
    public function setEndStatus(string $endStatus): DeadPlayerInfo
    {
        $this->endStatus = $endStatus;

        return $this;
    }
}
