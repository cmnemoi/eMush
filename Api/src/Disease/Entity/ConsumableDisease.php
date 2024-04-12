<?php

namespace Mush\Disease\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Enum\MedicalConditionTypeEnum;

#[ORM\Entity]
#[ORM\Table(name: 'disease_consummable')]
class ConsumableDisease
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Daedalus::class)]
    private Daedalus $daedalus;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\OneToMany(targetEntity: ConsumableDiseaseAttribute::class, mappedBy: 'consumableDisease', cascade: ['all'])]
    private Collection $diseaseAttributes;

    public function __construct()
    {
        $this->diseaseAttributes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): self
    {
        $this->daedalus = $daedalus;

        return $this;
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

    public function getDiseases(): Collection
    {
        return $this->diseaseAttributes->filter(static fn (ConsumableDiseaseAttribute $attribute) => $attribute->getType() === MedicalConditionTypeEnum::DISEASE);
    }

    public function getCures(): Collection
    {
        return $this->diseaseAttributes->filter(static fn (ConsumableDiseaseAttribute $attribute) => $attribute->getType() === MedicalConditionTypeEnum::CURE);
    }

    public function setDiseasesAttribute(Collection $diseaseAttributes): self
    {
        $this->diseaseAttributes = $diseaseAttributes;

        return $this;
    }

    public function addDiseaseAttribute(ConsumableDiseaseAttribute $diseaseAttribute): self
    {
        if (!$this->diseaseAttributes->contains($diseaseAttribute)) {
            $this->diseaseAttributes->add($diseaseAttribute);
        }

        return $this;
    }
}
