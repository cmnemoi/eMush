<?php

namespace Mush\Player\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Entity\LogParameter;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Mush\Player\Repository\PlayerRepository")
 */
class Player implements StatusHolderInterface, ActionParameter, LogParameter
{
    use TimestampableEntity;
    use TargetStatusTrait;

    /**
     * @ORM\Id
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
     * @ORM\ManyToOne (targetEntity="Mush\Daedalus\Entity\Daedalus", inversedBy="players")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\ManyToOne (targetEntity="Mush\Place\Entity\Place", inversedBy="players")
     */
    private Place $place;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Equipment\Entity\GameItem", mappedBy="player")
     */
    private Collection $items;

    /**
     * @ORM\OneToMany (targetEntity="Mush\Status\Entity\StatusTarget", mappedBy="player", cascade="ALL", orphanRemoval=true)
     */
    private Collection $statuses;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Disease\Entity\PlayerDisease", mappedBy="player")
     */
    private Collection $diseases;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private array $skills = [];

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $healthPoint = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $moralPoint = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $actionPoint = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $movementPoint = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $triumph = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $satiety = 0;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->statuses = new ArrayCollection();
        $this->diseases = new ArrayCollection();
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

    public function getPlace(): Place
    {
        return $this->place;
    }

    /**
     * @return static
     */
    public function setPlace(Place $place): Player
    {
        $this->place = $place;

        $place->addPlayer($this);

        return $this;
    }

    /**
     * Return true if the item is reachable for the player i.e. in the inventory or the room.
     */
    public function canReachEquipment(GameEquipment $gameEquipment): bool
    {
        if ($gameEquipment instanceof Door &&
            $this->getPlace()->getDoors()->contains($gameEquipment)
        ) {
            return true;
        }
        if ($hiddenStatus = $gameEquipment->getStatusByName(EquipmentStatusEnum::HIDDEN)) {
            return $hiddenStatus->getTarget() === $this;
        } else {
            return $this->items->contains($gameEquipment) || $this->getPlace()->getEquipments()->contains($gameEquipment);
        }
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
            $statusTarget->setPlayer($this);
            $this->statuses->add($statusTarget);
        }

        return $this;
    }

    public function isMush(): bool
    {
        return $this->hasStatus(PlayerStatusEnum::MUSH);
    }

    public function getDiseases(): Collection
    {
        return $this->diseases;
    }

    public function getDiseaseByName(string $diseaseName): ?PlayerDisease
    {
        $disease = $this->diseases->filter(fn (PlayerDisease $playerDisease) => ($playerDisease->getDiseaseConfig()->getName() === $diseaseName));

        return $disease->isEmpty() ? null : $disease->first();
    }

    public function setDiseases(Collection $diseases): Player
    {
        $this->diseases = $diseases;

        return $this;
    }

    public function addDisease(PlayerDisease $playerDisease): Player
    {
        $this->diseases->add($playerDisease);

        return $this;
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

    public function getClassName(): string
    {
        return get_class($this);
    }

    public function getSelfActions(): Collection
    {
        return $this->characterConfig->getActions()
            ->filter(fn (Action $action) => $action->getScope() === ActionScopeEnum::SELF);
    }

    public function getTargetActions(): Collection
    {
        return $this->characterConfig->getActions()
            ->filter(fn (Action $action) => $action->getScope() === ActionScopeEnum::OTHER_PLAYER);
    }
}
