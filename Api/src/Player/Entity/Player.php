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
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Repository\PlayerRepository;
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

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player implements StatusHolderInterface, LogParameterInterface, ModifierHolder, EquipmentHolderInterface
{
    use TimestampableEntity;
    use TargetStatusTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $user;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $gameStatus;

    #[ORM\ManyToOne(targetEntity: CharacterConfig::class)]
    private CharacterConfig $characterConfig;

    #[ORM\ManyToOne(targetEntity: Daedalus::class, inversedBy: 'players')]
    private Daedalus $daedalus;

    #[ORM\ManyToOne(targetEntity: Place::class, inversedBy: 'players')]
    private Place $place;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: GameItem::class)]
    private Collection $items;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: StatusTarget::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $statuses;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: PlayerDisease::class)]
    private Collection $medicalCondition;

    #[ORM\ManyToMany(targetEntity: Player::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'player_player_flirts')]
    private Collection $flirts;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: Modifier::class)]
    private Collection $modifiers;

    #[ORM\Column(type: 'array', nullable: true)]
    private array $skills = [];

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $healthPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $moralPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $actionPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $movementPoint = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $triumph = 0;

    #[ORM\Column(type: 'integer', nullable: false)]
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

    public function getName(): string
    {
        return $this->characterConfig->getName();
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }

    public function setGameStatus(string $gameStatus): static
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

    public function setCharacterConfig(CharacterConfig $characterConfig): static
    {
        $this->characterConfig = $characterConfig;

        return $this;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): static
    {
        $this->daedalus = $daedalus;

        $daedalus->addPlayer($this);

        return $this;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }

    public function setPlace(Place $place): static
    {
        $this->place = $place;
        $place->addPlayer($this);

        return $this;
    }

    public function changePlace(Place $place): static
    {
        if ($this->place !== $place) {
            $this->place->removePlayer($this);
        }

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
            $gameEquipment->getRooms()->contains($this->getPlace())
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

    public function setEquipments(ArrayCollection $equipments): static
    {
        $this->items = $equipments;

        return $this;
    }

    public function addEquipment(GameEquipment $gameEquipment): static
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

    public function removeEquipment(GameEquipment $gameEquipment): static
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

    public function addStatus(Status $status): static
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

    public function setMedicalCondition(Collection $medicalCondition): static
    {
        $this->medicalCondition = $medicalCondition;

        return $this;
    }

    public function addMedicalCondition(PlayerDisease $playerDisease): static
    {
        $this->medicalCondition->add($playerDisease);

        return $this;
    }

    public function getModifiers(): ModifierCollection
    {
        return new ModifierCollection($this->modifiers->toArray());
    }

    public function getAllModifiers(): ModifierCollection
    {
        $allModifiers = new ModifierCollection($this->modifiers->toArray());
        $allModifiers = $allModifiers->addModifiers($this->place->getModifiers());

        return $allModifiers->addModifiers($this->daedalus->getModifiers());
    }

    public function addModifier(Modifier $modifier): static
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

    public function setFlirts(Collection $flirts): static
    {
        $this->flirts = $flirts;

        return $this;
    }

    public function addFlirt(Player $playerFlirt): static
    {
        $this->flirts->add($playerFlirt);

        return $this;
    }

    public function HasFlirtedWith(Player $playerTarget): bool
    {
        return $this->getFlirts()->exists(fn (int $id, Player $player) => $player === $playerTarget);
    }

    public function addSkill(string $skill): static
    {
        $this->skills[] = $skill;

        return $this;
    }

    public function getSkills(): array
    {
        return $this->skills;
    }

    public function setSkills(array $skills): static
    {
        $this->skills = $skills;

        return $this;
    }

    public function getHealthPoint(): int
    {
        return $this->healthPoint;
    }

    public function setHealthPoint(int $healthPoint): static
    {
        $this->healthPoint = $healthPoint;

        return $this;
    }

    public function addHealthPoint(int $healthPoint): static
    {
        $this->healthPoint += $healthPoint;

        return $this;
    }

    public function getMoralPoint(): int
    {
        return $this->moralPoint;
    }

    public function setMoralPoint(int $moralPoint): static
    {
        $this->moralPoint = $moralPoint;

        return $this;
    }

    public function addMoralPoint(int $moralPoint): static
    {
        $this->moralPoint += $moralPoint;

        return $this;
    }

    public function getActionPoint(): int
    {
        return $this->actionPoint;
    }

    public function setActionPoint(int $actionPoint): static
    {
        $this->actionPoint = $actionPoint;

        return $this;
    }

    public function addActionPoint(int $actionPoint): static
    {
        $this->actionPoint += $actionPoint;

        return $this;
    }

    public function getMovementPoint(): int
    {
        return $this->movementPoint;
    }

    public function setMovementPoint(int $movementPoint): static
    {
        $this->movementPoint = $movementPoint;

        return $this;
    }

    public function addMovementPoint(int $movementPoint): static
    {
        $this->movementPoint += $movementPoint;

        return $this;
    }

    public function getTriumph(): int
    {
        return $this->triumph;
    }

    public function setTriumph(int $triumph): static
    {
        $this->triumph = $triumph;

        return $this;
    }

    public function addTriumph(int $triumph): static
    {
        $this->triumph += $triumph;

        return $this;
    }

    public function getSatiety(): int
    {
        return $this->satiety;
    }

    public function setSatiety(int $satiety): static
    {
        $this->satiety = $satiety;

        return $this;
    }

    public function addSatiety(int $satiety): static
    {
        $this->satiety += $satiety;

        return $this;
    }

    public function getVariableFromName(string $variableName): int
    {
        switch ($variableName) {
            case PlayerVariableEnum::MORAL_POINT:
                return $this->moralPoint;
            case PlayerVariableEnum::MOVEMENT_POINT:
                return $this->movementPoint;
            case PlayerVariableEnum::HEALTH_POINT:
                return $this->healthPoint;
            case PlayerVariableEnum::ACTION_POINT:
                return $this->actionPoint;
            case PlayerVariableEnum::SATIETY:
                return $this->satiety;
            case PlayerVariableEnum::TRIUMPH:
                return $this->triumph;
            default:
                throw new \LogicException('this is not a valid playerVariable');
        }
    }

    public function setVariableFromName(string $variableName, int $value): static
    {
        switch ($variableName) {
            case PlayerVariableEnum::MORAL_POINT:
                $this->moralPoint = $value;

                return $this;
            case PlayerVariableEnum::MOVEMENT_POINT:
                $this->movementPoint = $value;

                return $this;
            case PlayerVariableEnum::HEALTH_POINT:
                $this->healthPoint = $value;

                return $this;
            case PlayerVariableEnum::ACTION_POINT:
                $this->actionPoint = $value;

                return $this;
            case PlayerVariableEnum::SATIETY:
                $this->satiety = $value;

                return $this;
            case PlayerVariableEnum::TRIUMPH:
                $this->triumph = $value;

                return $this;
            default:
                throw new \LogicException('this is not a valid playerVariable');
        }
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
