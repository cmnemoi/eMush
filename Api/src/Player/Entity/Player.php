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
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableCollection;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Hunter\Entity\HunterTargetEntityInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
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
class Player implements StatusHolderInterface, LogParameterInterface, ModifierHolder, EquipmentHolderInterface, GameVariableHolderInterface, HunterTargetEntityInterface
{
    use TimestampableEntity;
    use TargetStatusTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToOne(mappedBy: 'player', targetEntity: PlayerInfo::class)]
    private PlayerInfo $playerInfo;

    #[ORM\ManyToOne(targetEntity: Daedalus::class, inversedBy: 'players')]
    private Daedalus $daedalus;

    #[ORM\ManyToOne(targetEntity: Place::class, inversedBy: 'players')]
    private Place $place;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: GameItem::class)]
    private Collection $items;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: StatusTarget::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $statuses;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: PlayerDisease::class)]
    private Collection $medicalConditions;

    #[ORM\ManyToMany(targetEntity: Player::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\JoinTable(name: 'player_player_flirts')]
    private Collection $flirts;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: GameModifier::class, cascade: ['REMOVE'])]
    private Collection $modifiers;

    #[ORM\Column(type: 'array', nullable: true)]
    private array $skills = [];

    #[ORM\OneToOne(targetEntity: GameVariableCollection::class, cascade: ['ALL'])]
    private PlayerVariables $playerVariables;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $triumph = 0;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->statuses = new ArrayCollection();
        $this->medicalConditions = new PlayerDiseaseCollection();
        $this->flirts = new PlayerCollection();
        $this->modifiers = new ModifierCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlayerInfo(): PlayerInfo
    {
        return $this->playerInfo;
    }

    public function setPlayerInfo(PlayerInfo $playerInfo): static
    {
        $this->playerInfo = $playerInfo;

        return $this;
    }

    public function getUser(): User
    {
        return $this->playerInfo->getUser();
    }

    public function getName(): string
    {
        return $this->playerInfo->getName();
    }

    public function isAlive(): bool
    {
        return $this->playerInfo->isAlive();
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
        if ($gameEquipment instanceof Door
            && $gameEquipment->getRooms()->contains($this->getPlace())
        ) {
            return true;
        }

        if ($gameEquipment->getEquipment()->isPersonal() && $gameEquipment->getOwner() !== $this) {
            return false;
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
        }

        return $this;
    }

    public function hasEquipmentByName(string $name): bool
    {
        return !$this->getEquipments()->filter(fn (GameItem $gameItem) => $gameItem->getName() === $name)->isEmpty();
    }

    public function hasOperationalEquipmentByName(string $name): bool
    {
        return !$this->getEquipments()->filter(fn (GameItem $gameItem) => $gameItem->getName() === $name
            && $gameItem->isOperational()
        )->isEmpty();
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
        if (!$this->medicalConditions instanceof PlayerDiseaseCollection) {
            $this->medicalConditions = new PlayerDiseaseCollection($this->medicalConditions->toArray());
        }

        return $this->medicalConditions;
    }

    public function getMedicalConditionByName(string $diseaseName): ?PlayerDisease
    {
        $disease = $this->medicalConditions->filter(fn (PlayerDisease $playerDisease) => ($playerDisease->getDiseaseConfig()->getDiseaseName() === $diseaseName));

        return $disease->isEmpty() ? null : $disease->first();
    }

    public function setMedicalConditions(Collection $medicalConditions): static
    {
        $this->medicalConditions = $medicalConditions;

        return $this;
    }

    public function addMedicalCondition(PlayerDisease $playerDisease): static
    {
        $this->medicalConditions->add($playerDisease);

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

    public function addModifier(GameModifier $modifier): static
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

    public function getGameVariables(): PlayerVariables
    {
        return $this->playerVariables;
    }

    public function setPlayerVariables(CharacterConfig $characterConfig): static
    {
        $this->playerVariables = new PlayerVariables($characterConfig);

        return $this;
    }

    public function getHealthPoint(): int
    {
        return $this->playerVariables->getValueByName(PlayerVariableEnum::HEALTH_POINT);
    }

    public function setHealthPoint(int $healthPoint): static
    {
        $this->playerVariables->setValueByName($healthPoint, PlayerVariableEnum::HEALTH_POINT);

        return $this;
    }

    public function getMoralPoint(): int
    {
        return $this->playerVariables->getValueByName(PlayerVariableEnum::MORAL_POINT);
    }

    public function setMoralPoint(int $moralPoint): static
    {
        $this->playerVariables->setValueByName($moralPoint, PlayerVariableEnum::MORAL_POINT);

        return $this;
    }

    public function getActionPoint(): int
    {
        return $this->playerVariables->getValueByName(PlayerVariableEnum::ACTION_POINT);
    }

    public function setActionPoint(int $actionPoint): static
    {
        $this->playerVariables->setValueByName($actionPoint, PlayerVariableEnum::ACTION_POINT);

        return $this;
    }

    public function getMovementPoint(): int
    {
        return $this->playerVariables->getValueByName(PlayerVariableEnum::MOVEMENT_POINT);
    }

    public function setMovementPoint(int $movementPoint): static
    {
        $this->playerVariables->setValueByName($movementPoint, PlayerVariableEnum::MOVEMENT_POINT);

        return $this;
    }

    public function getSatiety(): int
    {
        return $this->playerVariables->getValueByName(PlayerVariableEnum::SATIETY);
    }

    public function setSatiety(int $satiety): static
    {
        $this->playerVariables->setValueByName($satiety, PlayerVariableEnum::SATIETY);

        return $this;
    }

    public function getSpores(): int
    {
        return $this->playerVariables->getValueByName(PlayerVariableEnum::SPORE);
    }

    public function setSpores(int $spores): static
    {
        $this->playerVariables->setValueByName($spores, PlayerVariableEnum::SPORE);

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

    public function getVariableByName(string $variableName): GameVariable
    {
        return $this->playerVariables->getVariableByName($variableName);
    }

    public function hasVariable(string $variableName): bool
    {
        return $this->playerVariables->hasVariable($variableName);
    }

    public function getVariableValueByName(string $variableName): int
    {
        return $this->playerVariables->getValueByName($variableName);
    }

    public function setVariableValueByName(string $variableName, int $value): static
    {
        $this->playerVariables->setValueByName($value, $variableName);

        return $this;
    }

    public function getClassName(): string
    {
        return get_class($this);
    }

    public function getSelfActions(): Collection
    {
        return $this->playerInfo->getCharacterConfig()->getActions()
            ->filter(fn (Action $action) => $action->getScope() === ActionScopeEnum::SELF);
    }

    public function getTargetActions(): Collection
    {
        return $this->playerInfo->getCharacterConfig()->getActions()
            ->filter(fn (Action $action) => $action->getScope() === ActionScopeEnum::OTHER_PLAYER);
    }

    public function getLogName(): string
    {
        return $this->playerInfo->getName();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::CHARACTER;
    }

    public function getPlayer(): self
    {
        return $this;
    }

    public function getGameEquipment(): ?GameEquipment
    {
        return null;
    }

    public function canSeeSpaceBattle(): bool
    {
        $spaceBattleRooms = array_merge(
            RoomEnum::getPatrolships()->toArray(),
            RoomEnum::getTurrets()->toArray(),
        );

        return in_array($this->getPlace()->getName(), $spaceBattleRooms, true);
    }

    public function isInAPatrolShip(): bool
    {
        return $this->getPlace()->getType() === PlaceTypeEnum::PATROL_SHIP;
    }

    public function isInSpace(): bool
    {
        return $this->getPlace()->getType() === PlaceTypeEnum::SPACE;
    }

    public function isInSpaceBattle(): bool
    {
        return $this->isInAPatrolShip() || $this->isInSpace();
    }
}
