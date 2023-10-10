<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'planet')]
#[ORM\UniqueConstraint(name: 'unique_planet_for_daedalus', columns: ['name', 'orientation', 'distance', 'daedalus_id'])]
#[UniqueEntity(['name', 'orientation', 'distance', 'daedalus'])]
final class Planet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name = '';

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

    #[ORM\ManyToOne(targetEntity: Daedalus::class, inversedBy: 'planets')]
    private Daedalus $daedalus;

    public function __construct(Player $player)
    {
        $this->player = $player;
        $this->daedalus = $player->getDaedalus();
        $this->sectors = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
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

    public function setOrientation(string $orientation): self
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function getDistance(): int
    {
        return $this->distance;
    }

    public function setDistance(int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * @return Collection<int, PlanetSector>
     */
    public function getSectors(): Collection
    {
        return $this->sectors;
    }

    public function addSector(PlanetSector $sector): self
    {
        if (!$this->sectors->contains($sector)) {
            $this->sectors->add($sector);
            $sector->setPlanet($this);
        }

        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
