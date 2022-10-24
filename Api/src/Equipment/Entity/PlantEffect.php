<?php

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Mechanics\Plant;

#[ORM\Entity]
class PlantEffect
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Daedalus::class)]
    private Daedalus $daedalus;

    #[ORM\ManyToOne(targetEntity: Plant::class)]
    private Plant $plant;

    #[ORM\Column(type: 'integer', nullable: true)]
    private int $maturationTime;

    #[ORM\Column(type: 'integer', nullable: true)]
    private int $oxygen;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): static
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getPlant(): Plant
    {
        return $this->plant;
    }

    public function setPlant(Plant $plant): static
    {
        $this->plant = $plant;

        return $this;
    }

    public function getMaturationTime(): int
    {
        return $this->maturationTime;
    }

    public function setMaturationTime(int $maturationTime): static
    {
        $this->maturationTime = $maturationTime;

        return $this;
    }

    public function getOxygen(): int
    {
        return $this->oxygen;
    }

    public function setOxygen(int $oxygen): static
    {
        $this->oxygen = $oxygen;

        return $this;
    }
}
