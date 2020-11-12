<?php

namespace Mush\Player\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Item\Entity\Door;
use Mush\Item\Entity\GameItem;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Collection\MedicalConditionCollection;
use Mush\Status\Entity\MedicalCondition;
use Mush\Status\Entity\Status;
use Mush\User\Entity\User;

/**
 * Class Player.
 *
 * @ORM\Entity(repositoryClass="Mush\Player\Repository\PlayerRepository")
 */
class Player
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\User\Entity\User")
     */
    private User $user;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private string $gameStatus;

    /**
     * Character is a reserved keyword for sql.
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private string $person;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Daedalus\Entity\Daedalus", inversedBy="players")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Room\Entity\Room", inversedBy="players")
     */
    private Room $room;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Item\Entity\GameItem", mappedBy="player")
     */
    private Collection $items;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Status\Entity\Status", mappedBy="player", cascade={"ALL"}, orphanRemoval=true)
     */
    private Collection $statuses;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private array $skills = [];

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $healthPoint;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $moralPoint;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $actionPoint;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $movementPoint;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $satiety;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->statuses = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): Player
    {
        $this->user = $user;

        return $this;
    }

    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }

    public function setGameStatus(string $gameStatus): Player
    {
        $this->gameStatus = $gameStatus;

        return $this;
    }

    public function getPerson(): string
    {
        return $this->person;
    }

    public function setPerson(string $person): Player
    {
        $this->person = $person;

        return $this;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): Player
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getRoom(): Room
    {
        return $this->room;
    }

    public function setRoom(Room $room): Player
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Return true if the item is reachable for the player i.e. in the inventory or the room.
     */
    public function canReachItem(GameItem $gameItem): bool
    {
        if ($gameItem instanceof Door &&
            $this->getRoom()->getDoors()->contains($gameItem)
        ) {
            return true;
        }

        return $this->items->contains($gameItem) || $this->room->getItems()->contains($gameItem);
    }

    public function getReachableItemByName(string $name): Collection
    {
        return (new ArrayCollection(array_merge(
            $this->getItems()->toArray(),
            $this->getRoom()->getItems()->toArray()
        ))
          )->filter(fn (GameItem $gameItem) => $gameItem->getName() === $name);
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function setItems(Collection $items): Player
    {
        $this->items = $items;

        return $this;
    }

    public function addItem(GameItem $item): Player
    {
        if (!$this->getItems()->contains($item)) {
            $this->getItems()->add($item);
            $item->setPlayer($this);
        }

        return $this;
    }

    public function removeItem(GameItem $item): Player
    {
        if ($this->items->contains($item)) {
            $this->items->removeElement($item);
            $item->setPlayer(null);
        }

        return $this;
    }

    public function hasItemByName(string $name): bool
    {
        return !$this->getItems()->filter(fn (GameItem $gameItem) => $gameItem->getName() === $name)->isEmpty();
    }

    public function getMedicalConditions(): MedicalConditionCollection
    {
        return new MedicalConditionCollection(
            $this->statuses->filter(fn (Status $status) => ($status instanceof MedicalCondition))->toArray()
        );
    }

    public function getStatuses(): Collection
    {
        return $this->statuses;
    }

    public function getStatusByName(string $name): ?Status
    {
        $status = $this->statuses->filter(fn (Status $status) => ($status->getName() === $name))->first();

        return $status ? $status : null;
    }

    public function setStatuses(Collection $statuses): Player
    {
        $this->statuses = $statuses;

        return $this;
    }

    public function addStatus(Status $status): Player
    {
        if (!$this->getStatuses()->contains($status)) {
            $this->statuses->add($status);
            $status->setPlayer($this);
        }

        return $this;
    }

    public function removeStatus(Status $status): Player
    {
        if ($this->statuses->contains($status)) {
            $this->statuses->removeElement($status);
            $status->setPlayer(null);
        }

        return $this;
    }

    public function addSkill(string $skill): Player
    {
        $this->skills[] = $skill;

        return $this;
    }

    public function getSkills(): ?array
    {
        return $this->skills;
    }

    public function setSkills(array $skills): Player
    {
        $this->skills = $skills;

        return $this;
    }

    public function getHealthPoint(): int
    {
        return $this->healthPoint;
    }

    public function setHealthPoint(int $healthPoint): Player
    {
        $this->healthPoint = $healthPoint;

        return $this;
    }

    public function addHealthPoint(int $healthPoint): Player
    {
        $this->healthPoint += $healthPoint;

        return $this;
    }

    public function getMoralPoint(): int
    {
        return $this->moralPoint;
    }

    public function setMoralPoint(int $moralPoint): Player
    {
        $this->moralPoint = $moralPoint;

        return $this;
    }

    public function addMoralPoint(int $moralPoint): Player
    {
        $this->moralPoint += $moralPoint;

        return $this;
    }

    public function getActionPoint(): int
    {
        return $this->actionPoint;
    }

    public function setActionPoint(int $actionPoint): Player
    {
        $this->actionPoint = $actionPoint;

        return $this;
    }

    public function addActionPoint(int $actionPoint): Player
    {
        $this->actionPoint += $actionPoint;

        return $this;
    }

    public function getMovementPoint(): int
    {
        return $this->movementPoint;
    }

    public function setMovementPoint(int $movementPoint): Player
    {
        $this->movementPoint = $movementPoint;

        return $this;
    }

    public function addMovementPoint(int $movementPoint): Player
    {
        $this->movementPoint += $movementPoint;
        if ($this->getMovementPoint() < 0) {
            $this->addActionPoint(-1);
            $this->addMovementPoint(3); //TODO improve conversion with disabled and scooter
        }

        return $this;
    }

    public function getSatiety(): int
    {
        return $this->satiety;
    }

    public function setSatiety(int $satiety): Player
    {
        $this->satiety = $satiety;

        return $this;
    }
}
