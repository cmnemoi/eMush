<?php

namespace Mush\Place\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionProviderOperationalStateEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Enum\SkillEnum;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Repository\PlaceRepository;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
#[ORM\Table(name: 'room')]
class Place implements StatusHolderInterface, ModifierHolderInterface, EquipmentHolderInterface, LogParameterInterface, ActionProviderInterface
{
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

    #[ORM\OneToMany(mappedBy: 'place', targetEntity: GameModifier::class, cascade: ['REMOVE'])]
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

    public static function createByName(string $name): self
    {
        $place = new self();
        $place
            ->setName($name)
            ->setType(PlaceTypeEnum::ROOM);

        return $place;
    }

    public static function createRoomByNameInDaedalus(string $name, Daedalus $daedalus): self
    {
        $place = self::createByName($name);
        $place->setDaedalus($daedalus);

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

    public function getBrokenEquipments(): Collection
    {
        return $this->getEquipments()->filter(static fn (GameEquipment $gameEquipment) => $gameEquipment->isBroken());
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
     * @return ArrayCollection<int, Place>
     */
    public function getAdjacentRooms(): ArrayCollection
    {
        /** @var ArrayCollection<int, Place> $adjacentRooms */
        $adjacentRooms = new ArrayCollection();
        foreach ($this->getDoors() as $door) {
            $adjacentRooms->add($door->getOtherRoom($this));
        }

        return $adjacentRooms;
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

    public function getAllModifiers(): ModifierCollection
    {
        $allModifiers = new ModifierCollection($this->modifiers->toArray());

        return $allModifiers->addModifiers($this->daedalus->getModifiers());
    }

    public function addModifier(GameModifier $modifier): static
    {
        $this->modifiers->add($modifier);

        return $this;
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

    public function getOperationalStatus(ActionEnum $actionName): ActionProviderOperationalStateEnum
    {
        $charge = $this->getUsedCharge($actionName);
        if ($charge !== null && !$charge->isCharged()) {
            return ActionProviderOperationalStateEnum::DISCHARGED;
        }

        return ActionProviderOperationalStateEnum::OPERATIONAL;
    }

    public function getUsedCharge(ActionEnum $actionName): ?ChargeStatus
    {
        $charges = $this->statuses->filter(static fn (Status $status) => $status instanceof ChargeStatus && $status->hasDischargeStrategy($actionName->value));

        $charge = $charges->first();
        if (!$charge instanceof ChargeStatus) {
            return null;
        }

        return $charge;
    }

    public function getAliveShrinks(): PlayerCollection
    {
        return $this->getPlayers()->getPlayerAlive()->filter(static fn (Player $player) => $player->hasSkill(SkillEnum::SHRINK));
    }

    public function hasAnAliveShrink(): bool
    {
        return $this->getAliveShrinks()->count() > 0;
    }
}
