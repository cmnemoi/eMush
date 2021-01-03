<?php

namespace Mush\Player\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Room\Entity\Room;
use Mush\Status\Entity\Collection\MedicalConditionCollection;
use Mush\Status\Entity\MedicalCondition;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
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
     * @ORM\ManyToOne (targetEntity="Mush\Game\Entity\CharacterConfig")
     */
    private CharacterConfig $characterConfig;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $endStatus = null;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Daedalus\Entity\Daedalus", inversedBy="players")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Room\Entity\Room", inversedBy="players")
     */
    private Room $room;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Equipment\Entity\GameItem", mappedBy="player")
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
    private int $triumph = 0;

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

    /**
     * @return static
     */
    public function setUser(User $user): Player
    {
        $this->user = $user;

        return $this;
    }

    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }

    /**
     * @return static
     */
    public function setGameStatus(string $gameStatus): Player
    {
        $this->gameStatus = $gameStatus;

        return $this;
    }

    public function isAlive(): bool
    {
        return $this->gameStatus === GameStatusEnum::CURRENT;
    }

    public function getCharacterConfig(): CharacterConfig
    {
        return $this->characterConfig;
    }

    /**
     * @return static
     */
    public function setCharacterConfig(CharacterConfig $characterConfig): Player
    {
        $this->characterConfig = $characterConfig;

        return $this;
    }

    public function getEndStatus(): ?string
    {
        return $this->endStatus;
    }

    /**
     * @return static
     */
    public function setEndStatus(string $endStatus): Player
    {
        $this->endStatus = $endStatus;

        return $this;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    /**
     * @return static
     */
    public function setDaedalus(Daedalus $daedalus): Player
    {
        $this->daedalus = $daedalus;

        $daedalus->addPlayer($this);

        return $this;
    }

    public function getRoom(): Room
    {
        return $this->room;
    }

    /**
     * @return static
     */
    public function setRoom(Room $room): Player
    {
        $this->room = $room;

        $room->addPlayer($this);

        return $this;
    }

    /**
     * Return true if the item is reachable for the player i.e. in the inventory or the room.
     */
    public function canReachEquipment(GameEquipment $gameEquipment): bool
    {
        if ($gameEquipment instanceof Door &&
            $this->getRoom()->getDoors()->contains($gameEquipment)
        ) {
            return true;
        }
        if ($hiddenStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::HIDDEN)) {
            return $hiddenStatus->getPlayer() === $this;
        } else {
            return $this->items->contains($gameEquipment) || $this->getRoom()->getEquipments()->contains($gameEquipment);
        }
    }

    public function getReachableEquipmentsByName(string $name, string $reach = ReachEnum::SHELVE_NOT_HIDDEN): Collection
    {
        //reach can be set to inventory, shelve, shelve only or any room of the Daedalus
        if ($reach === ReachEnum::INVENTORY) {
            return $this->getItems()->filter(fn (GameItem $gameItem) => $gameItem->getName() === $name);
        } elseif ($reach === ReachEnum::SHELVE_NOT_HIDDEN) {
            return (new ArrayCollection(array_merge(
                $this->getItems()->toArray(),
                $this->getRoom()->getEquipments()->toArray()
            ))
            )->filter(fn (GameEquipment $gameEquipment) => (
                $gameEquipment->getName() === $name &&
                (($hiddenStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::HIDDEN)) === null ||
                    $hiddenStatus->getPlayer() === $this)));
        } elseif ($reach === ReachEnum::SHELVE) {
            return (new ArrayCollection(array_merge(
                $this->getItems()->toArray(),
                $this->getRoom()->getEquipments()->toArray()
            ))
            )->filter(fn (GameEquipment $equipment) => ($equipment->getName() === $name));
        } else {
            return $this->getDaedalus()
                ->getRoomByName($reach)
                ->getEquipments()
                ->filter(fn (GameEquipment $equipment) => $equipment->getName() === $name)
                ;
        }
    }

    public function getReachableTools(): Collection
    {
        //reach can be set to inventory, shelve, shelve only or any room of the Daedalus
        return (new ArrayCollection(array_merge($this->getItems()->toArray(), $this->getRoom()->getEquipments()->toArray())
        ))->filter(fn (GameEquipment $gameEquipment) => ($gameEquipment->getEquipment()->getMechanicbyName(EquipmentMechanicEnum::TOOL)));
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @return static
     */
    public function setItems(Collection $items): Player
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return static
     */
    public function addItem(GameItem $item): Player
    {
        if (!$this->getItems()->contains($item)) {
            if ($item->getPlayer() !== $this) {
                $item->setPlayer(null);
            }

            $this->getItems()->add($item);
            $item->setPlayer($this);
        }

        return $this;
    }

    /**
     * @return static
     */
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

    /**
     * @return static
     */
    public function setStatuses(Collection $statuses): Player
    {
        $this->statuses = $statuses;

        return $this;
    }

    /**
     * @return static
     */
    public function addStatus(Status $status): Player
    {
        if (!$this->getStatuses()->contains($status)) {
            if ($status->getPlayer() !== $this) {
                $status->setPlayer(null);
            }

            $this->statuses->add($status);

            $status->setPlayer($this);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function removeStatus(Status $status): Player
    {
        if ($this->statuses->contains($status)) {
            $this->statuses->removeElement($status);
            $status->setPlayer(null);
        }

        return $this;
    }

    public function isMush(): bool
    {
        return $this
            ->getStatuses()
            ->exists(fn (int $key, Status $status) => ($status->getName() === PlayerStatusEnum::MUSH))
            ;
    }

    /**
     * @return static
     */
    public function addSkill(string $skill): Player
    {
        $this->skills[] = $skill;

        return $this;
    }

    public function getSkills(): array
    {
        return $this->skills;
    }

    /**
     * @return static
     */
    public function setSkills(array $skills): Player
    {
        $this->skills = $skills;

        return $this;
    }

    public function getHealthPoint(): int
    {
        return $this->healthPoint;
    }

    /**
     * @return static
     */
    public function setHealthPoint(int $healthPoint): Player
    {
        $this->healthPoint = $healthPoint;

        return $this;
    }

    /**
     * @return static
     */
    public function addHealthPoint(int $healthPoint): Player
    {
        $this->healthPoint += $healthPoint;

        return $this;
    }

    public function getMoralPoint(): int
    {
        return $this->moralPoint;
    }

    /**
     * @return static
     */
    public function setMoralPoint(int $moralPoint): Player
    {
        $this->moralPoint = $moralPoint;

        return $this;
    }

    /**
     * @return static
     */
    public function addMoralPoint(int $moralPoint): Player
    {
        $this->moralPoint += $moralPoint;

        return $this;
    }

    public function getActionPoint(): int
    {
        return $this->actionPoint;
    }

    /**
     * @return static
     */
    public function setActionPoint(int $actionPoint): Player
    {
        $this->actionPoint = $actionPoint;

        return $this;
    }

    /**
     * @return static
     */
    public function addActionPoint(int $actionPoint): Player
    {
        $this->actionPoint += $actionPoint;

        return $this;
    }

    public function getMovementPoint(): int
    {
        return $this->movementPoint;
    }

    /**
     * @return static
     */
    public function setMovementPoint(int $movementPoint): Player
    {
        $this->movementPoint = $movementPoint;

        return $this;
    }

    /**
     * @return static
     */
    public function addMovementPoint(int $movementPoint): Player
    {
        $this->movementPoint += $movementPoint;
        if ($this->getMovementPoint() < 0) {
            $this->addActionPoint(-1);
            $this->addMovementPoint(3); //TODO improve conversion with disabled and scooter
        }

        return $this;
    }

    public function getTriumph(): int
    {
        return $this->triumph;
    }

    /**
     * @return static
     */
    public function setTriumph(int $triumph): Player
    {
        $this->triumph = $triumph;

        return $this;
    }

    /**
     * @return static
     */
    public function addTriumph(int $triumph): Player
    {
        $this->triumph += $triumph;

        return $this;
    }

    public function getSatiety(): int
    {
        return $this->satiety;
    }

    /**
     * @return static
     */
    public function setSatiety(int $satiety): Player
    {
        $this->satiety = $satiety;

        return $this;
    }

    /**
     * @return static
     */
    public function addSatiety(int $satiety): Player
    {
        $this->satiety += $satiety;

        return $this;
    }
}
