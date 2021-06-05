<?php

namespace Mush\Disease\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;

/**
 * @ORM\Entity
 * @ORM\Table(name="disease_consummable")
 */
class ConsumableDisease
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Daedalus\Entity\Daedalus")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $name;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Disease\Entity\ConsumableDiseaseCharacteristic", mappedBy="consumableDisease" , cascade="all")
     */
    private Collection $diseases;

    public function __construct()
    {
        $this->diseases = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): ConsumableDisease
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ConsumableDisease
    {
        $this->name = $name;

        return $this;
    }

    public function getDiseases(): Collection
    {
        return $this->diseases;
    }

    public function setDiseases(Collection $diseases): ConsumableDisease
    {
        $this->diseases = $diseases;

        return $this;
    }

    public function addDisease(ConsumableDiseaseCharacteristic $disease): ConsumableDisease
    {
        if (!$this->diseases->contains($disease)) {
            $this->diseases->add($disease);
        }

        return $this;
    }
}
