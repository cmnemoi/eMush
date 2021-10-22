<?php

namespace Mush\Player\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Collection\PlayerDiseaseCollection;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @ORM\Entity(repositoryClass="Mush\Player\Repository\PlayerRepository")
 */
class Player implements StatusHolderInterface, LogParameterInterface, ModifierHolder, EquipmentHolderInterface
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
     * @ORM\ManyToOne (targetEntity="Mush\Player\Entity\Config\CharacterConfig")
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
     * @ORM\OneToMany (targetEntity="Mush\Status\Entity\StatusTarget", mappedBy="player", cascade={"ALL"}, orphanRemoval=true)
     */
    private Collection $statuses;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Disease\Entity\PlayerDisease", mappedBy="player")
     */
    private Collection $medicalCondition;

    /**
     * @ORM\ManyToMany (targetEntity="Mush\Player\Entity\Player", cascade={"ALL"}, orphanRemoval=true)
     * @ORM\JoinTable(name="player_player_flirts")
     */
    private Collection $flirts;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Modifier\Entity\Modifier", mappedBy="player")
     */
    private Collection $modifiers;

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
        $this->medicalCondition = new PlayerDiseaseCollection();
        $this->flirts = new PlayerCollection();
        $this->modifiers = new ModifierCollection();
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
    public function setUser(User $user): self
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
    public function setGameStatus(string $gameStatus): self
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
    public function setCharacterConfig(CharacterConfig $characterConfig): self
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
    public function setDaedalus(Daedalus $daedalus): self
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
    public function setPlace(Place $place): self
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

    public function getEquipments(): Collection
    {
        return $this->items;
    }

    /**
     * @return static
     */
    public function setEquipments(ArrayCollection $equipments): self
    {
        $this->items = $equipments;

        return $this;
    }

    /**
     * @return static
     */
    public function addEquipment(GameEquipment $gameEquipment): self
    {
        if (!$gameEquipment instanceof GameItem) {
            throw new UnexpectedTypeException($gameEquipment, GameItem::class);
        }
        if (!$this->getEquipments()->contains($gameEquipment)) {
            $this->getEquipments()->add($gameEquipment);
            $gameEquipment->setHolder($this);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function removeEquipment(GameEquipment $gameEquipment): self
    {
        if ($this->items->contains($gameEquipment)) {
            $this->items->removeElement($gameEquipment);
            $gameEquipment->setHolder(null);
        }

        return $this;
    }

    public function hasItemByName(string $name): bool
    {
        return !$this->getEquipments()->filter(fn (GameItem $gameItem) => $gameItem->getName() === $name)->isEmpty();
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

    public function getMedicalConditions(): PlayerDiseaseCollection
    {
        if (!$this->medicalCondition instanceof PlayerDiseaseCollection) {
            $this->medicalCondition = new PlayerDiseaseCollection($this->medicalCondition->toArray());
        }

        return $this->medicalCondition;
    }

    public function getMedicalConditionByName(string $diseaseName): ?PlayerDisease
    {
        $disease = $this->medicalCondition->filter(fn (PlayerDisease $playerDisease) => ($playerDisease->getDiseaseConfig()->getName() === $diseaseName));

        return $disease->isEmpty() ? null : $disease->first();
    }

    public function setMedicalCondition(Collection $medicalCondition): self
    {
        $this->medicalCondition = $medicalCondition;

        return $this;
    }

    public function addMedicalCondition(PlayerDisease $playerDisease): self
    {
        $this->medicalCondition->add($playerDisease);

        return $this;
    }

    public function getModifiers(): ModifierCollection
    {
        return new ModifierCollection($this->modifiers->toArray());
    }

    /**
     * @return static
     */
    public function addModifier(Modifier $modifier): self
    {
        $this->modifiers->add($modifier);

        return $this;
    }

    public function getFlirts(): PlayerCollection
    {
        if (!$this->flirts instanceof PlayerCollection) {
            $this->flirts = new PlayerCollection($this->flirts->toArray());
        }

        return $this->flirts;
    }

    public function setFlirts(Collection $flirts): self
    {
        $this->flirts = $flirts;

        return $this;
    }

    public function addFlirt(Player $playerFlirt): self
    {
        $this->flirts->add($playerFlirt);

        return $this;
    }

    public function HasFlirtedWith(Player $playerTarget): bool
    {
        return $this->getFlirts()->exists(fn (int $id, Player $player) => $player === $playerTarget);
    }

    /**
     * @return static
     */
    public function addSkill(string $skill): self
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
    public function setSkills(array $skills): self
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
    public function setHealthPoint(int $healthPoint): self
    {
        $this->healthPoint = $healthPoint;

        return $this;
    }

    /**
     * @return static
     */
    public function addHealthPoint(int $healthPoint): self
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
    public function setMoralPoint(int $moralPoint): self
    {
        $this->moralPoint = $moralPoint;

        return $this;
    }

    /**
     * @return static
     */
    public function addMoralPoint(int $moralPoint): self
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
    public function setActionPoint(int $actionPoint): self
    {
        $this->actionPoint = $actionPoint;

        return $this;
    }

    /**
     * @return static
     */
    public function addActionPoint(int $actionPoint): self
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
    public function setMovementPoint(int $movementPoint): self
    {
        $this->movementPoint = $movementPoint;

        return $this;
    }

    /**
     * @return static
     */
    public function addMovementPoint(int $movementPoint): self
    {
        $this->movementPoint += $movementPoint;

        return $this;
    }

    public function getTriumph(): int
    {
        return $this->triumph;
    }

    /**
     * @return static
     */
    public function setTriumph(int $triumph): self
    {
        $this->triumph = $triumph;

        return $this;
    }

    /**
     * @return static
     */
    public function addTriumph(int $triumph): self
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
    public function setSatiety(int $satiety): self
    {
        $this->satiety = $satiety;

        return $this;
    }

    /**
     * @return static
     */
    public function addSatiety(int $satiety): self
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

    public function getLogName(): string
    {
        return $this->getCharacterConfig()->getName();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::CHARACTER;
    }
}
