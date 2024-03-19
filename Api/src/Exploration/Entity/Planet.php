<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

#[ORM\Entity]
#[ORM\Table(name: 'planet')]
class Planet implements LogParameterInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: PlanetName::class, cascade: ['ALL'])]
    private PlanetName $name;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $size = 0;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $orientation = '';

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $distance = 0;

    #[ORM\OneToMany(mappedBy: 'planet', targetEntity: PlanetSector::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $sectors;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'planets')]
    private Player $player;

    #[ORM\OneToOne(targetEntity: Exploration::class, mappedBy: 'planet', cascade: ['remove'], orphanRemoval: true)]
    private ?Exploration $exploration = null;

    public function __construct(Player $player)
    {
        $this->player = $player;
        $this->player->addPlanet($this);
        $this->sectors = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): PlanetName
    {
        return $this->name;
    }

    public function setName(PlanetName $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getOrientation(): string
    {
        return $this->orientation;
    }

    public function getDistance(): int
    {
        return $this->distance;
    }

    public function getCoordinates(): SpaceCoordinates
    {
        return new SpaceCoordinates($this->orientation, $this->distance);
    }

    public function setCoordinates(SpaceCoordinates $coordinates): self
    {
        $this->orientation = $coordinates->getOrientation();
        $this->distance = $coordinates->getDistance();

        return $this;
    }

    /** @return Collection<int, PlanetSector> */
    public function getSectors(): Collection
    {
        return $this->sectors;
    }

    /** @param Collection<int, PlanetSector> $sectors */
    public function setSectors(Collection $sectors): self
    {
        $this->sectors = $sectors;

        return $this;
    }

    public function addSector(PlanetSector $sector): self
    {
        $this->sectors->add($sector);

        return $this;
    }

    public function hasSectorByName(string $name): bool
    {
        return $this->sectors->exists(fn ($key, PlanetSector $sector) => $sector->getName() === $name);
    }

    /** @return Collection<int, PlanetSector> */
    public function getRevealedSectors(): Collection
    {
        return $this->sectors->filter(fn (PlanetSector $sector) => $sector->isRevealed());
    }

    /** @return Collection<int, PlanetSector> */
    public function getUnrevealedSectors(): Collection
    {
        return $this->sectors->filter(fn (PlanetSector $sector) => !$sector->isRevealed());
    }

    /** @return Collection<int, PlanetSector> */
    public function getVisitedSectors(): Collection
    {
        return $this->sectors->filter(fn (PlanetSector $sector) => $sector->isVisited());
    }

    /** @return Collection<int, PlanetSector> */
    public function getUnvisitedSectors(): Collection
    {
        return $this->sectors->filter(fn (PlanetSector $sector) => !$sector->isVisited());
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getExploration(): ?Exploration
    {
        return $this->exploration;
    }

    public function setExploration(?Exploration $exploration): self
    {
        $this->exploration = $exploration;

        return $this;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->player->getDaedalus();
    }

    public function getClassName(): string
    {
        return self::class;
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::PLANET;
    }

    public function getLogName(): string
    {
        return $this->name->toString();
    }
}
