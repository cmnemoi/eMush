<?php

namespace Mush\Place\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierHolderTrait;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Repository\PlaceRepository;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;
use Mush\Status\Enum\PlayerStatusEnum;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
#[ORM\Table(name: 'room')]
class Place implements StatusHolderInterface, ModifierHolderInterface, EquipmentHolderInterface, LogParameterInterface, ActionProviderInterface
{
    use ModifierHolderTrait;
    use TargetStatusTrait;
    use TimestampableEntity;

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

    #[ORM\OneToMany(mappedBy: 'place', targetEntity: ModifierHolder::class, cascade: ['REMOVE'])]
    private Collection $modifiers;

    #[ORM\OneToMany(mappedBy: 'space', targetEntity: Hunter::class, cascade: ['REMOVE'], orphanRemoval: true)]
    private Collection $hunters;

    public function __construct()
    {
        $this->players = new PlayerCollection();
        $this->equipments = new ArrayCollection();
        $this->doors = new ArrayCollection();
        $this->statuses = new ArrayCollection();
        $this->modifiers = new ModifierCollection();
        $this->hunters = new ArrayCollection();
    }

    public static function createRoomByName(string $name): self
    {
        $place = new self();
        $place
            ->setName($name)
            ->setType(PlaceTypeEnum::ROOM);

        return $place;
    }

    public static function createRoomByNameInDaedalus(string $name, Daedalus $daedalus): self
    {
        $place = self::createRoomByName($name);
        $place->setDaedalus($daedalus);

        return $place;
    }

    public static function createNull(): self
    {
        $place = new self();
        $place
            ->setName(RoomEnum::null)
            ->setType('');
        (new \ReflectionProperty($place, 'id'))->setValue($place, 0);

        return $place;
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

    public function getPlayerByName(string $name): Player
    {
        return $this->getPlayers()->getPlayerByName($name) ?: Player::createNull();
    }

    public function getAlivePlayersExcept(Player $player): PlayerCollection
    {
        return $this->getPlayers()->getPlayerAlive()->getAllExcept($player);
    }

    /** /!\ Do not use this method if you want the number of players ALIVE ! Use `$place->getNumberOfPlayersAlive()` instead. /!\ */
    public function getNumberPlayers(): int
    {
        if (!$this->players instanceof PlayerCollection) {
            $this->players = new PlayerCollection($this->players->toArray());
        }

        return $this->players->count();
    }

    public function getNumberOfPlayersAlive(): int
    {
        if (!$this->players instanceof PlayerCollection) {
            $this->players = new PlayerCollection($this->players->toArray());
        }

        return $this->players->getPlayerAlive()->count();
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
        }

        return $this;
    }

    public function hasEquipmentByName(string $name): bool
    {
        return !$this->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name)->isEmpty();
    }

    public function hasOperationalEquipmentByName(string $name): bool
    {
        return !$this->getEquipments()->filter(
            static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name
            && $gameEquipment->isOperational()
        )->isEmpty();
    }

    public function getEquipmentByName(string $name): ?GameEquipment
    {
        return $this->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name)->first() ?: null;
    }

    public function getEquipmentByNameOrThrow(string $name): GameEquipment
    {
        $equipment = $this->getEquipmentByName($name);
        if ($equipment === null) {
            throw new \RuntimeException("There should be a {$name} equipment in the place");
        }

        return $equipment;
    }

    public function getFirstEquipmentByMechanicNameOrThrow(string $mechanicName): GameEquipment
    {
        /** @var GameEquipment $equipment */
        foreach ($this->equipments as $equipment) {
            if ($equipment->hasMechanicByName($mechanicName)) {
                return $equipment;
            }
        }

        throw new \RuntimeException("There should be an equipment with {$mechanicName} mechanic in the place {$this->name}");
    }

    /**
     * @return Collection<int, GameEquipment>
     */
    public function getAllEquipmentsByName(string $name): Collection
    {
        return $this->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name);
    }

    /**
     * @return Collection<array-key, Door>
     */
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

    /**
     * @return Collection<array-key, Door>
     */
    public function getOperationalDoors(): Collection
    {
        return $this->getDoors()->filter(static fn (Door $door) => $door->isOperational());
    }

    /**
     * @return Collection<array-key, GameEquipment>
     */
    public function getBrokenDoorsAndEquipments(): Collection
    {
        $brokenEquipments = $this->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isBroken())->toArray();
        $brokenDoors = $this->getDoors()->filter(static fn (Door $door) => $door->isBroken())->toArray();

        return new ArrayCollection(array_merge($brokenEquipments, $brokenDoors));
    }

    /**
     * This method returns all rooms connected to this one by a door.
     * /!\ Do NOT use this method if you want rooms with a working door ! Use `$place->getAccessibleRooms()` instead. /!\.
     *
     * @return Collection<array-key, Place>
     */
    public function getAdjacentRooms(): Collection
    {
        return $this->getDoors()->map(fn (Door $door) => $door->getOtherRoom($this));
    }

    /**
     * This method returns all rooms connected to this one by a working door.
     *
     * @return Collection<array-key, Place>
     */
    public function getAccessibleRooms(): Collection
    {
        return $this->getOperationalDoors()->map(fn (Door $door) => $door->getOtherRoom($this));
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

    public function getAllModifiers(): ModifierCollection
    {
        $allModifiers = $this->getModifiers();

        return $allModifiers->addModifiers($this->daedalus->getModifiers());
    }

    public function getAttackingHunters(): HunterCollection
    {
        return (new HunterCollection($this->hunters->toArray()))->getAttackingHunters();
    }

    public function getHunterPool(): HunterCollection
    {
        return (new HunterCollection($this->hunters->toArray()))->getHunterPool();
    }

    public function setHunters(ArrayCollection $hunters): static
    {
        $this->hunters = $hunters;

        return $this;
    }

    public function addHunter(Hunter $hunter): static
    {
        if (!$this->hunters->contains($hunter)) {
            $this->hunters->add($hunter);

            $hunter->setSpace($this);
        }

        return $this;
    }

    public function removeHunter(Hunter $hunter): static
    {
        if ($this->hunters->contains($hunter)) {
            $this->hunters->removeElement($hunter);
        }

        return $this;
    }

    public function getClassName(): string
    {
        return static::class;
    }

    public function getGameEquipment(): null
    {
        return null;
    }

    public function getPlace(): self
    {
        return $this;
    }

    public function getPlayer(): null
    {
        return null;
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::PLACE;
    }

    public function getLogName(): string
    {
        return $this->getName();
    }

    // return actions provided by this entity and the other action provider it bears $actionRange should always be set to ROOM
    public function getProvidedActions(ActionHolderEnum $actionTarget, array $actionRanges): Collection
    {
        $actions = [];

        // then actions provided by the statuses
        /** @var Status $status */
        foreach ($this->getStatuses() as $status) {
            $actions = array_merge($actions, $status->getProvidedActions($actionTarget, $actionRanges)->toArray());
        }

        // then actions provided by the equipment in shelve
        /** @var GameItem $equipment */
        foreach ($this->getEquipments() as $equipment) {
            $actions = array_merge($actions, $equipment->getProvidedActions($actionTarget, $actionRanges)->toArray());
        }

        // then players in the room
        /** @var Player $player */
        foreach ($this->getPlayers() as $player) {
            $actions = array_merge($actions, $player->getProvidedActions($actionTarget, $actionRanges)->toArray());
        }

        return new ArrayCollection($actions);
    }

    public function canPlayerReach(Player $player): bool
    {
        return $this === $player->getPlace();
    }

    public function getOperationalStatus(string $actionName): ActionProviderOperationalStateEnum
    {
        $charge = $this->getUsedCharge($actionName);
        if ($charge !== null && !$charge->isCharged()) {
            return ActionProviderOperationalStateEnum::DISCHARGED;
        }

        return ActionProviderOperationalStateEnum::OPERATIONAL;
    }

    public function getUsedCharge(string $actionName): ?ChargeStatus
    {
        $charges = $this->statuses->filter(static fn (Status $status) => $status instanceof ChargeStatus && $status->hasDischargeStrategy($actionName));

        $charge = $charges->first();
        if (!$charge instanceof ChargeStatus) {
            return null;
        }

        return $charge;
    }

    public function getAliveShrinksExceptPlayer(Player $player): PlayerCollection
    {
        return $this
            ->getPlayers()
            ->getPlayersWithSkill(SkillEnum::SHRINK)
            ->getPlayerAlive()
            ->getAllExcept($player);
    }

    public function hasAnAliveShrinkExceptPlayer(Player $player): bool
    {
        return $this->getAliveShrinksExceptPlayer($player)->count() > 0;
    }

    public function hasAGuardian(): bool
    {
        return $this->getPlayers()->getPlayerAlive()->hasOneWithStatus(PlayerStatusEnum::GUARDIAN);
    }

    public function hasAlivePlayerWithSkill(SkillEnum $skill): bool
    {
        return $this->getPlayers()->getPlayersWithSkill($skill)->getPlayerAlive()->count() > 0;
    }
}
