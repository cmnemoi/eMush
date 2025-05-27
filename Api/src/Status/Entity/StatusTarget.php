<?php

namespace Mush\Status\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Hunter\Entity\Hunter;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

#[ORM\Entity]
class StatusTarget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'owner', targetEntity: Status::class, cascade: ['ALL'])]
    private ?Status $owner = null;

    #[ORM\OneToOne(mappedBy: 'target', targetEntity: Status::class, cascade: ['ALL'])]
    private ?Status $target = null;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'statuses')]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: GameEquipment::class, inversedBy: 'statuses')]
    private ?GameEquipment $gameEquipment = null;

    #[ORM\ManyToOne(targetEntity: Place::class, inversedBy: 'statuses')]
    private ?Place $place = null;

    #[ORM\ManyToOne(targetEntity: Daedalus::class, inversedBy: 'statuses')]
    private ?Daedalus $daedalus = null;

    #[ORM\ManyToOne(targetEntity: Hunter::class, inversedBy: 'statuses')]
    private ?Hunter $hunter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?Status
    {
        return $this->owner;
    }

    public function setOwner(Status $owner): self
    {
        $this->owner = $owner;
        $owner->setTargetOwner($this);

        return $this;
    }

    public function getTarget(): ?Status
    {
        return $this->target;
    }

    public function setTarget(Status $target): self
    {
        $this->target = $target;
        $target->setStatusTargetTarget($this);

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): self
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

    public function setGameEquipment(?GameEquipment $gameEquipment): self
    {
        $this->gameEquipment = $gameEquipment;

        if ($gameEquipment !== null) {
            $gameEquipment->addStatusTarget($this);
        }

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;
        $place?->addStatusTarget($this);

        return $this;
    }

    public function getHunter(): ?Hunter
    {
        return $this->hunter;
    }

    public function setHunter(?Hunter $hunter): self
    {
        $this->hunter = $hunter;

        if ($hunter !== null) {
            $hunter->addStatusTarget($this);
        }

        return $this;
    }

    public function getDaedalus(): ?Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(?Daedalus $daedalus): self
    {
        $this->daedalus = $daedalus;

        if ($daedalus !== null) {
            $daedalus->addStatusTarget($this);
        }

        return $this;
    }

    public function removeStatusLinksTarget(): void
    {
        $this->owner = null;
        $this->target = null;
        $this->player = null;
        $this->gameEquipment = null;
        $this->place = null;
        $this->daedalus = null;
        $this->hunter = null;
    }
}
