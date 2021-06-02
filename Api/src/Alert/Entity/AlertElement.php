<?php

namespace Mush\Alert\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

/**
 * @ORM\Entity
 * @ORM\Table(name="alert_element")
 */
class AlertElement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Alert\Entity\Alert")
     */
    private Alert $alert;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Player\Entity\Player")
     */
    private ?Player $player = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Place\Entity\Place")
     */
    private ?Place $place = null;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Equipment\Entity\GameEquipment")
     */
    private ?GameEquipment $equipment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAlert(Alert $alert): AlertElement
    {
        $this->alert = $alert;

        return $this;
    }

    public function getAlert(): Alert
    {
        return $this->alert;
    }

    public function setPlayer(Player $player): AlertElement
    {
        $this->player = $player;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlace(Place $place): AlertElement
    {
        $this->place = $place;

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setEquipment(GameEquipment $equipment): AlertElement
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getEquipment(): ?GameEquipment
    {
        return $this->equipment;
    }
}
