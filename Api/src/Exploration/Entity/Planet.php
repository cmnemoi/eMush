<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;

#[ORM\Entity]
#[ORM\Table(name: 'planet')]
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

    public function __construct(Player $player)
    {
        $this->player = $player;
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

    /** @param Collection<int, PlanetSector> $sectors */
    public function setSectors(Collection $sectors): self
    {
        $this->sectors = $sectors;

        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->player->getDaedalus();
    }
}
