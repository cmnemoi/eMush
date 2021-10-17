<?php

namespace Mush\Equipment\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\Mechanics\Plant;

/**
 * Class PlantEffect.
 *
 * @ORM\Entity
 */
class PlantEffect
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Daedalus\Entity\Daedalus")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Equipment\Entity\Mechanics\Plant")
     */
    private Plant $plant;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $maturationTime;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $oxygen;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    /**
     * @return static
     */
    public function setDaedalus(Daedalus $daedalus): PlantEffect
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getPlant(): Plant
    {
        return $this->plant;
    }

    /**
     * @return static
     */
    public function setPlant(Plant $plant): PlantEffect
    {
        $this->plant = $plant;

        return $this;
    }

    public function getMaturationTime(): int
    {
        return $this->maturationTime;
    }

    /**
     * @return static
     */
    public function setMaturationTime(int $maturationTime): PlantEffect
    {
        $this->maturationTime = $maturationTime;

        return $this;
    }

    public function getOxygen(): int
    {
        return $this->oxygen;
    }

    /**
     * @return static
     */
    public function setOxygen(int $oxygen): PlantEffect
    {
        $this->oxygen = $oxygen;

        return $this;
    }
}
