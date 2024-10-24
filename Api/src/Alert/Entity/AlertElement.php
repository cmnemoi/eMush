<?php

namespace Mush\Alert\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\PlayerInfo;

#[ORM\Entity]
#[ORM\Table(name: 'alert_element')]
class AlertElement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Version]
    #[ORM\Column(type: 'integer', length: 255, nullable: false, options: ['default' => 1])]
    private int $version = 1;

    #[ORM\ManyToOne(targetEntity: Alert::class, inversedBy: 'alertElements')]
    private Alert $alert;

    #[ORM\ManyToOne(targetEntity: PlayerInfo::class)]
    private ?PlayerInfo $playerInfo = null;

    #[ORM\ManyToOne(targetEntity: Place::class)]
    private ?Place $place = null;

    #[ORM\ManyToOne(targetEntity: GameEquipment::class)]
    private ?GameEquipment $equipment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAlert(Alert $alert): self
    {
        $this->alert = $alert;

        return $this;
    }

    public function getAlert(): Alert
    {
        return $this->alert;
    }

    public function setPlayerInfo(PlayerInfo $playerInfo): self
    {
        $this->playerInfo = $playerInfo;

        return $this;
    }

    public function getPlayerInfo(): ?PlayerInfo
    {
        return $this->playerInfo;
    }

    public function setPlace(Place $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setEquipment(GameEquipment $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getEquipment(): ?GameEquipment
    {
        return $this->equipment;
    }
}
