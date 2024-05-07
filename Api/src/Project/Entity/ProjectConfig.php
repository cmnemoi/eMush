<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;

#[ORM\Entity]
class ProjectConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: ProjectName::class, options: ['default' => ProjectName::NULL])]
    private ProjectName $name;

    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: ProjectType::class, options: ['default' => ProjectType::NULL])]
    private ProjectType $type;

    #[ORM\Column(type: 'integer', length: 255, nullable: false, options: ['default' => 0])]
    private int $efficiency;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $bonusSkills;

    #[ORM\Column(type: 'integer', length: 255, nullable: false, options: ['default' => 100])]
    private int $activationRate;

    #[ORM\ManyToMany(targetEntity: AbstractModifierConfig::class)]
    private Collection $modifierConfigs;

    #[ORM\ManyToMany(targetEntity: AbstractEventConfig::class)]
    private Collection $activationEventConfigs;

    public function __construct(
        ProjectName $name = ProjectName::NULL,
        ProjectType $type = ProjectType::NULL,
        int         $efficiency = 0,
        array       $bonusSkills = [],
        int         $activationRate = 100,
        array       $activationEvents = [],
        array       $modifierConfigs = [],
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->efficiency = $efficiency;
        $this->bonusSkills = $bonusSkills;
        $this->activationRate = $activationRate;
        $this->activationEventConfigs = new ArrayCollection($activationEvents);
        $this->modifierConfigs = new ArrayCollection($modifierConfigs);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ProjectName
    {
        return $this->name;
    }

    public function getType(): ProjectType
    {
        return $this->type;
    }

    public function getEfficiency(): int
    {
        return $this->efficiency;
    }

    public function getBonusSkills(): array
    {
        return $this->bonusSkills;
    }

    public function getActivationRate(): int
    {
        return $this->activationRate;
    }

    public function getModifierConfigs(): Collection
    {
        return $this->modifierConfigs;
    }

    public function getActivationEventsConfigs(): Collection
    {
        return $this->activationEventConfigs;
    }

    public function updateFromConfigData(array $configData): void
    {
        $this->name = $configData['name'];
        $this->type = $configData['type'];
        $this->efficiency = $configData['efficiency'];
        $this->bonusSkills = $configData['bonusSkills'];
        $this->activationRate = $configData['activationRate'];
        $this->activationEventConfigs = new ArrayCollection($configData['activationEvents']);
        $this->modifierConfigs = new ArrayCollection($configData['modifierConfigs']);
    }
}
