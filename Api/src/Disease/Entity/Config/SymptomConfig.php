<?php

namespace Mush\Disease\Entity\Config;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Disease\Entity\SymptomCondition;
use Mush\Game\Enum\VisibilityEnum;

#[ORM\Entity]
#[ORM\Table(name: 'symptom_config')]
class SymptomConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $trigger = 'None';

    #[ORM\Column(type: 'string', nullable: false)]
    private string $visibility = VisibilityEnum::PUBLIC;

    #[ORM\ManyToMany(targetEntity: SymptomCondition::class)]
    private Collection $symptomConditions;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->symptomConditions = new ArrayCollection([]);
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

    public function addSymptomCondition(SymptomCondition $symptomCondition): self
    {
        $this->symptomConditions->add($symptomCondition);

        return $this;
    }
}
