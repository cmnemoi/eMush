<?php

namespace Mush\Disease\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Enum\VisibilityEnum;

#[ORM\Entity]
#[ORM\Table(name: 'symptom_config')]
class SymptomConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $symptomName;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $trigger = 'None';

    #[ORM\Column(type: 'string', nullable: false)]
    private string $visibility = VisibilityEnum::PUBLIC;

    #[ORM\ManyToMany(targetEntity: SymptomCondition::class)]
    private Collection $symptomConditions;

    public function __construct(string $symptomName)
    {
        $this->symptomName = $symptomName;
        $this->symptomConditions = new ArrayCollection([]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setSymptomName(string $symptomName): self
    {
        $this->symptomName = $symptomName;

        return $this;
    }

    public function getSymptomName(): string
    {
        return $this->symptomName;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function buildName(string $configName, ?string $details = null): self
    {
        if ($details === null) {
            $this->name = $this->symptomName . '_ON_' . $this->getTrigger() . '_' . $configName;
        } else {
            $this->name = $this->symptomName . '_ON_' . $this->getTrigger() . '_' . $details . '_' . $configName;
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): self
    {
        $this->trigger = $trigger;

        return $this;
    }

    public function getClassName(): string
    {
        return SymptomConfig::class;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getSymptomConditions(): Collection
    {
        return $this->symptomConditions;
    }

    public function setSymptomConditions(Collection|array $symptomConditions): self
    {
        if (is_array($symptomConditions)) {
            $symptomConditions = new ArrayCollection($symptomConditions);
        }

        $this->symptomConditions = $symptomConditions;

        return $this;
    }

    public function addSymptomCondition(SymptomCondition $symptomCondition): self
    {
        $this->symptomConditions->add($symptomCondition);

        return $this;
    }
}
