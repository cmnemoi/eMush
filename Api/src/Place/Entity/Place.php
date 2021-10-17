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
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;

/**
 * Class Room.
 *
 * @ORM\Entity(repositoryClass="Mush\Place\Repository\PlaceRepository")
 */
class Place implements StatusHolderInterface, ModifierHolder, EquipmentHolderInterface
{
    use TimestampableEntity;
    use TargetStatusTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $type = PlaceTypeEnum::ROOM;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Daedalus\Entity\Daedalus", inversedBy="places")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Player\Entity\Player", mappedBy="place")
     */
    private Collection $players;

    /**
     * @ORM\ManyToMany (targetEntity="Mush\Equipment\Entity\Door", cascade={"persist"}, orphanRemoval=true)
     */
    private Collection $doors;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Equipment\Entity\GameEquipment", mappedBy="place", orphanRemoval=true)
     */
    private Collection $equipments;

    /**
     * @ORM\OneToMany (targetEntity="Mush\Status\Entity\StatusTarget", mappedBy="place", cascade={"ALL"}, orphanRemoval=true)
     */
    private Collection $statuses;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Modifier\Entity\Modifier", mappedBy="place")
     */
    private Collection $modifiers;

    public function __construct()
    {
        $this->players = new PlayerCollection();
        $this->equipments = new ArrayCollection();
        $this->doors = new ArrayCollection();
        $this->statuses = new ArrayCollection();
        $this->modifiers = new ModifierCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return static
     */
    public function setName(string $name): Place
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Place
    {
        $this->type = $type;

        return $this;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    /**
     * @return static
     */
    public function setDaedalus(Daedalus $daedalus): Place
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

    /**
     * @return static
     */
    public function setPlayers(ArrayCollection $players): Place
    {
        $this->players = $players;

        return $this;
    }

    /**
     * @return static
     */
    public function addPlayer(Player $player): Place
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
    public function removePlayer(Player $player): Place
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getEquipments(): Collection
    {
        return $this->equipments;
    }

    /**
     * @return static
     */
    public function setEquipments(ArrayCollection $equipments): self
    {
        $this->equipments = $equipments;

        return $this;
    }

    /**
     * @return static
     */
    public function addEquipment(GameEquipment $gameEquipment): self
    {
        if (!$this->equipments->contains($gameEquipment)) {
            $this->equipments->add($gameEquipment);
            $gameEquipment->setHolder($this);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function removeEquipment(GameEquipment $gameEquipment): self
    {
        if ($this->equipments->contains($gameEquipment)) {
            $this->equipments->removeElement($gameEquipment);
            $gameEquipment->setHolder(null);
        }

        return $this;
    }

    public function getDoors(): Collection
    {
        return $this->doors;
    }

    /**
     * @return static
     */
    public function setDoors(ArrayCollection $doors): Place
    {
        $this->doors = $doors;
        foreach ($doors as $door) {
            if (!$door->getRooms()->contains($this)) {
                $door->addRoom($this);
            }
        }

        return $this;
    }

    /**
     * @return static
     */
    public function addDoor(Door $door): Place
    {
        $this->doors->add($door);
        if (!$door->getRooms()->contains($this)) {
            $door->addRoom($this);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function addStatus(Status $status): self
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

    /**
     * @return static
     */
    public function addModifier(Modifier $modifier): Place
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
