<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;

/**
 * @ORM\Entity()
 */
class StatusTarget
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Status\Entity\Status")
     */
    private Status $status;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Player\Entity\Player")
     */
    private ?Player $player = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Equipment\Entity\GameEquipment")
     */
    private ?GameEquipment $gameEquipment = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Room\Entity\Room")
     */
    private ?Room $room = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): StatusTarget
    {
        $this->status = $status;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): StatusTarget
    {
        $this->player = $player;

        return $this;
    }

    public function getGameEquipment(): ?GameEquipment
    {
        return $this->gameEquipment;
    }

    public function setGameEquipment(?GameEquipment $gameEquipment): StatusTarget
    {
        $this->gameEquipment = $gameEquipment;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): StatusTarget
    {
        $this->room = $room;

        return $this;
    }
}
