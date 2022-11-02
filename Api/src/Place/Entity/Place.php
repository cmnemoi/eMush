<?php

namespace Mush\Place\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Repository\PlaceRepository;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
#[ORM\Table(name: 'room')]
class Place implements StatusHolderInterface, ModifierHolder, EquipmentHolderInterface
{
    use TimestampableEntity;
    use TargetStatusTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $type = PlaceTypeEnum::ROOM;

    #[ORM\ManyToOne(targetEntity: Daedalus::class, inversedBy: 'places')]
    private Daedalus $daedalus;

    #[ORM\OneToMany(mappedBy: 'place', targetEntity: Player::class)]
    private Collection $players;

    #[ORM\ManyToMany(targetEntity: Door::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $doors;

    #[ORM\OneToMany(mappedBy: 'place', targetEntity: GameEquipment::class, orphanRemoval: true)]
    private Collection $equipments;

    #[ORM\OneToMany(mappedBy: 'place', targetEntity: StatusTarget::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $statuses;

    #[ORM\OneToMany(mappedBy: 'place', targetEntity: Modifier::class)]
    private Collection $modifiers;

    public function __construct()
    {
        $this->players = new PlayerCollection();
        $this->equipments = new ArrayCollection();
        $this->doors = new ArrayCollection();
        $this->statuses = new ArrayCollection();
        $this->modifiers = new ModifierCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): static
    {
        $this->daedalus = $daedalus;

        $daedalus->addPlace($this);

        return $this;
    }

    public function getPlayers(): PlayerCollection
    {
        if (!$this->players instanceof PlayerCollection) {
            $this->players = new PlayerCollection($this->players->toArray());
        }

        return $this->players;
    }

    public function getNumberPlayers(): int
    {
        if (!$this->players instanceof PlayerCollection) {
            $this->players = new PlayerCollection($this->players->toArray());
        }

        return $this->players->count();
    }

    public function setPlayers(ArrayCollection $players): static
    {
        $this->players = $players;

        return $this;
    }

    /**
     * @return static
     */
    public function addPlayer(Player $player): self
    {
        if (!$this->getPlayers()->contains($player)) {
            $this->players->add($player);
            $player->setPlace($this);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function removePlayer(Player $player): self
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getEquipments(): Collection
    {
        return $this->equipments;
    }

    public function setEquipments(ArrayCollection $equipments): static
    {
        $this->equipments = $equipments;

        return $this;
    }

    public function addEquipment(GameEquipment $gameEquipment): static
    {
        if (!$this->equipments->contains($gameEquipment)) {
            $this->equipments->add($gameEquipment);
            $gameEquipment->setHolder($this);
        }

        return $this;
    }

    public function removeEquipment(GameEquipment $gameEquipment): static
    {
        if ($this->equipments->contains($gameEquipment)) {
            $this->equipments->removeElement($gameEquipment);
            $gameEquipment->setHolder(null);
        }

        return $this;
    }

    public function hasEquipmentByName(string $name): bool
    {
        return !$this->getEquipments()->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name)->isEmpty();
    }

    public function hasOperationalEquipmentByName(string $name): bool
    {
        return !$this->getEquipments()->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name &&
            $gameEquipment->isOperational()
        )->isEmpty();
    }

    public function getDoors(): Collection
    {
        return $this->doors;
    }

    public function setDoors(ArrayCollection $doors): static
    {
        $this->doors = $doors;
        foreach ($doors as $door) {
            if (!$door->getRooms()->contains($this)) {
                $door->addRoom($this);
            }
        }

        return $this;
    }

    public function addDoor(Door $door): static
    {
        $this->doors->add($door);
        if (!$door->getRooms()->contains($this)) {
            $door->addRoom($this);
        }

        return $this;
    }

    public function addStatus(Status $status): static
    {
        if (!$this->getStatuses()->contains($status)) {
            if (!$statusTarget = $status->getStatusTargetTarget()) {
                $statusTarget = new StatusTarget();
            }
            $statusTarget->setOwner($status);
            $statusTarget->setPlace($this);
            $this->statuses->add($statusTarget);
        }

        return $this;
    }

    public function getModifiers(): ModifierCollection
    {
        return new ModifierCollection($this->modifiers->toArray());
    }

    public function getModifiersAtReach(): ModifierCollection
    {
        $allModifiers = new ModifierCollection($this->modifiers->toArray());

        return $allModifiers->addModifiers($this->daedalus->getModifiers());
    }

    public function addModifier(Modifier $modifier): static
    {
        $this->modifiers->add($modifier);

        return $this;
    }

    public function getClassName(): string
    {
        return get_class($this);
    }

    public function getPlace(): self
    {
        return $this;
    }
}
