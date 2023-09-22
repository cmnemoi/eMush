<?php

namespace Mush\Disease\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\Collection\ProbaCollection;

#[ORM\Entity]
#[ORM\Table(name: 'disease_cause_config')]
class DiseaseCauseConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $causeName;

    #[ORM\Column(type: 'array')]
    private array $diseases;

    public function __construct()
    {
        $this->diseases = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCauseName(): string
    {
        return $this->causeName;
    }

    public function setCauseName(string $causeName): self
    {
        $this->causeName = $causeName;

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

    public function buildName(string $configName): self
    {
        $this->name = $this->causeName . '_' . $configName;

        return $this;
    }

    public function getDiseases(): ProbaCollection
    {
        return new ProbaCollection($this->diseases);
    }

    public function setDiseases(array $diseases): self
    {
        $this->diseases = $diseases;

        return $this;
    }
}
