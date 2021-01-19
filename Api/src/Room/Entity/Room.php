<?php

namespace Mush\Room\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

/**
 * Class Room.
 *
 * @ORM\Entity(repositoryClass="Mush\Room\Repository\RoomRepository")
 */
class Room implements StatusHolderInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $name;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Daedalus\Entity\Daedalus", inversedBy="rooms")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Player\Entity\Player", mappedBy="room")
     */
    private Collection $players;

    /**
     * @ORM\ManyToMany (targetEntity="Mush\Equipment\Entity\Door", cascade={"persist"}, orphanRemoval=true)
     */
    private Collection $doors;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Equipment\Entity\GameEquipment", mappedBy="room", cascade={"persist"}, orphanRemoval=true)
     */
    private Collection $equipments;

    /**
     * @ORM\ManyToMany(targetEntity="Mush\Status\Entity\Status")
     * @ORM\JoinTable(name="statuses_room",
     *      joinColumns={@ORM\JoinColumn(name="game_equipment_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="status_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private Collection $statuses;

    public function __construct()
    {
        $this->players = new PlayerCollection();
        $this->equipments = new ArrayCollection();
        $this->doors = new ArrayCollection();
        $this->statuses = new ArrayCollection();
    }

    public function getId(): int
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
    public function setName(string $name): Room
    {
        $this->name = $name;

        return $this;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    /**
     * @return static
     */
    public function setDaedalus(Daedalus $daedalus): Room
    {
        $this->daedalus = $daedalus;

        $daedalus->addRoom($this);

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
    public function setPlayers(ArrayCollection $players): Room
    {
        $this->players = $players;

        return $this;
    }

    /**
     * @return static
     */
    public function addPlayer(Player $player): Room
    {
        if (!$this->getPlayers()->contains($player)) {
            $this->players->add($player);
            $player->setRoom($this);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function removePlayer(Player $player): Room
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
    public function setEquipments(ArrayCollection $equipments): Room
    {
        $this->equipments = $equipments;

        return $this;
    }

    /**
     * @return static
     */
    public function addEquipment(GameEquipment $equipment): Room
    {
        if (!$this->equipments->contains($equipment)) {
            $this->equipments->add($equipment);
            $equipment->setRoom($this);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function removeEquipment(GameEquipment $equipment): Room
    {
        if ($this->equipments->contains($equipment)) {
            $this->equipments->removeElement($equipment);
            $equipment->setRoom(null);
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
    public function setDoors(ArrayCollection $doors): Room
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
    public function addDoor(Door $door): Room
    {
        $this->doors->add($door);
        if (!$door->getRooms()->contains($this)) {
            $door->addRoom($this);
        }

        return $this;
    }

    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    /**
     * @return static
     */
    public function setStatuses(Collection $statuses): Room
    {
        $this->statuses = $statuses;

        return $this;
    }

    /**
     * @return static
     */
    public function addStatus(Status $status): Room
    {
        if (!$this->getStatuses()->contains($status)) {
            $this->statuses->add($status);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function removeStatus(Status $status): Room
    {
        if ($this->statuses->contains($status)) {
            $this->statuses->removeElement($status);
        }

        return $this;
    }

    public function hasStatus(string $statusName): bool
    {
        return $this->statuses->exists(fn ($key, Status $status) => ($status->getName() === $statusName));
    }

    public function getStatusByName(string $statusName): ?Status
    {
        $status = $this->statuses->filter(fn (Status $status) => ($status->getName() === $statusName))->first();

        return $status ? $status : null;
    }
}
