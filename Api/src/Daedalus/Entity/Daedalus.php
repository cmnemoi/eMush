<?php

namespace Mush\Daedalus\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableCollection;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

#[ORM\Entity(repositoryClass: DaedalusRepository::class)]
#[ORM\Table(name: 'daedalus')]
class Daedalus implements ModifierHolder, GameVariableHolderInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToOne(mappedBy: 'daedalus', targetEntity: DaedalusInfo::class)]
    private DaedalusInfo $daedalusInfo;

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: Player::class)]
    private Collection $players;

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: Place::class)]
    private Collection $places;

    #[ORM\OneToMany(mappedBy: 'daedalus', targetEntity: GameModifier::class, cascade: ['REMOVE'])]
    private Collection $modifiers;

    #[ORM\OneToOne(targetEntity: GameVariableCollection::class, cascade: ['ALL'])]
    private DaedalusVariables $daedalusVariables;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $day = 1;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $cycle = 1;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $filledAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $finishedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $cycleStartedAt = null;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $isCycleChange = false;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->places = new ArrayCollection();
        $this->modifiers = new ModifierCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDaedalusInfo(): DaedalusInfo
    {
        return $this->daedalusInfo;
    }

    public function setDaedalusInfo(DaedalusInfo $daedalusInfo): static
    {
        $this->daedalusInfo = $daedalusInfo;

        return $this;
    }

    public function getPlayers(): PlayerCollection
    {
        return new PlayerCollection($this->players->toArray());
    }

    public function setPlayers(Collection $players): static
    {
        $this->players = $players;

        return $this;
    }

    public function addPlayer(Player $player): static
    {
        if (!$this->getPlayers()->contains($player)) {
            $this->players->add($player);

            $player->setDaedalus($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): static
    {
        $this->players->removeElement($player);

        return $this;
    }

    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function getRooms(): Collection
    {
        return $this->getPlaces()->filter(fn (Place $place) => $place->getType() === PlaceTypeEnum::ROOM);
    }

    public function getPlaceByName(string $name): ?Place
    {
        $place = $this->getPlaces()->filter(fn (Place $place) => $place->getName() === $name)->first();

        return $place === false ? null : $place;
    }

    public function setPlaces(Collection $places): static
    {
        $this->places = $places;

        return $this;
    }

    public function addPlace(Place $place): static
    {
        if (!$this->getPlaces()->contains($place)) {
            $this->places->add($place);

            $place->setDaedalus($this);
        }

        return $this;
    }

    public function removePlace(Place $place): static
    {
        $this->places->removeElement($place);

        return $this;
    }

    public function getModifiers(): ModifierCollection
    {
        return new ModifierCollection($this->modifiers->toArray());
    }

    public function getAllModifiers(): ModifierCollection
    {
        return new ModifierCollection($this->modifiers->toArray());
    }

    public function addModifier(GameModifier $modifier): static
    {
        $this->modifiers->add($modifier);

        return $this;
    }

    public function getVariableByName(string $variableName): GameVariable
    {
        return $this->daedalusVariables->getVariableByName($variableName);
    }

    public function getVariableValueByName(string $variableName): int
    {
        return $this->daedalusVariables->getValueByName($variableName);
    }

    public function setVariableValueByName(int $value, string $variableName): static
    {
        $this->daedalusVariables->setValueByName($value, $variableName);

        return $this;
    }

    public function getGameVariables(): DaedalusVariables
    {
        return $this->daedalusVariables;
    }

    public function hasVariable(string $variableName): bool
    {
        return $this->daedalusVariables->hasVariable($variableName);
    }

    public function setDaedalusVariables(DaedalusConfig $daedalusConfig): static
    {
        $this->daedalusVariables = new DaedalusVariables($daedalusConfig);

        return $this;
    }

    public function getOxygen(): int
    {
        return $this->getVariableValueByName(DaedalusVariableEnum::OXYGEN);
    }

    public function setOxygen(int $oxygen): static
    {
        $this->setVariableValueByName($oxygen, DaedalusVariableEnum::OXYGEN);

        return $this;
    }

    public function getFuel(): int
    {
        return $this->getVariableValueByName(DaedalusVariableEnum::FUEL);
    }

    public function setFuel(int $fuel): static
    {
        $this->setVariableValueByName($fuel, DaedalusVariableEnum::FUEL);

        return $this;
    }

    public function getHull(): int
    {
        return $this->getVariableValueByName(DaedalusVariableEnum::HULL);
    }

    public function setHull(int $hull): static
    {
        $this->setVariableValueByName($hull, DaedalusVariableEnum::HULL);

        return $this;
    }

    public function getShield(): int
    {
        return $this->getVariableValueByName(DaedalusVariableEnum::SHIELD);
    }

    public function setShield(int $shield): static
    {
        $this->setVariableValueByName($shield, DaedalusVariableEnum::SHIELD);

        return $this;
    }

    public function getSpores(): int
    {
        return $this->getVariableValueByName(DaedalusVariableEnum::SPORE);
    }

    public function setSpores(int $spores): static
    {
        $this->setVariableValueByName($spores, DaedalusVariableEnum::SPORE);

        return $this;
    }

    public function getCycle(): int
    {
        return $this->cycle;
    }

    public function setCycle(int $cycle): static
    {
        $this->cycle = $cycle;

        return $this;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function setDay(int $day): static
    {
        $this->day = $day;

        return $this;
    }

    public function getFilledAt(): ?\DateTime
    {
        return $this->filledAt;
    }

    public function setFilledAt(\DateTime $filledAt): static
    {
        $this->filledAt = $filledAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTime
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(\DateTime $finishedAt): static
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getCycleStartedAt(): ?\DateTime
    {
        return $this->cycleStartedAt;
    }

    public function setCycleStartedAt(\DateTime $cycleStartedAt): static
    {
        $this->cycleStartedAt = $cycleStartedAt;

        return $this;
    }

    public function isCycleChange(): bool
    {
        return $this->isCycleChange;
    }

    public function setIsCycleChange(bool $isCycleChange): static
    {
        $this->isCycleChange = $isCycleChange;

        return $this;
    }

    public function getClassName(): string
    {
        return get_class($this);
    }

    public function getLanguage(): string
    {
        return $this->daedalusInfo->getLocalizationConfig()->getLanguage();
    }

    public function getGameConfig(): GameConfig
    {
        return $this->daedalusInfo->getGameConfig();
    }

    public function getGameStatus(): string
    {
        return $this->daedalusInfo->getGameStatus();
    }

    public function getName(): string
    {
        return $this->daedalusInfo->getName();
    }
}
