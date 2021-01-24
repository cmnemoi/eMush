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
     * @ORM\OneToOne(targetEntity="Mush\Status\Entity\Status", mappedBy="owner", cascade="ALL")
     */
    private ?Status $owner = null;

    /**
     * @ORM\OneToOne(targetEntity="Mush\Status\Entity\Status", mappedBy="target", cascade="ALL")
     */
    private ?Status $target = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Player\Entity\Player", inversedBy="statuses")
     */
    private ?Player $player = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Equipment\Entity\GameEquipment", inversedBy="statuses")
     */
    private ?GameEquipment $gameEquipment = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Room\Entity\Room", inversedBy="statuses")
     */
    private ?Room $room = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Status
     */
    public function getOwner(): ?Status
    {
        return $this->owner;
    }

    public function setOwner(Status $owner): StatusTarget
    {
        $this->owner = $owner;
        $owner->setTargetOwner($this);

        return $this;
    }

    public function getTarget(): ?Status
    {
        return $this->target;
    }

    public function setTarget(Status $target): StatusTarget
    {
        $this->target = $target;
        $target->setStatusTargetTarget($this);

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): StatusTarget
    {
        $this->player = $player;

        if ($player !== null) {
            $player->addStatusTarget($this);
        }

        return $this;
    }

    public function getGameEquipment(): ?GameEquipment
    {
        return $this->gameEquipment;
    }

    public function setGameEquipment(?GameEquipment $gameEquipment): StatusTarget
    {
        $this->gameEquipment = $gameEquipment;

        if ($gameEquipment !== null) {
            $gameEquipment->addStatusTarget($this);
        }

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): StatusTarget
    {
        $this->room = $room;

        if ($room !== null) {
            $room->addStatusTarget($this);
        }

        return $this;
    }

    public function removeStatusLinksTarget(): void
    {
        $this->owner = null;
        $this->target = null;
        $this->player = null;
        $this->gameEquipment = null;
        $this->room = null;
    }
}
