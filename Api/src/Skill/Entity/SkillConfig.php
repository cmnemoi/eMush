<?php

declare(strict_types=1);

namespace Mush\Skill\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\Entity\Config\SpawnEquipmentConfig;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Skill\Enum\SkillName;
use Mush\Status\Entity\Config\StatusConfig;

#[ORM\Entity]
class SkillConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: SkillName::class, options: ['default' => SkillName::NULL])]
    private SkillName $name;

    #[ORM\ManyToOne(targetEntity: SpawnEquipmentConfig::class, cascade: ['persist'])]
    private ?SpawnEquipmentConfig $spawnEquipmentConfig;

    #[ORM\ManyToOne(targetEntity: StatusConfig::class, cascade: ['persist'])]
    private ?StatusConfig $specialistPointsConfig;

    #[ORM\ManyToMany(targetEntity: AbstractModifierConfig::class)]
    private Collection $modifierConfigs;

    #[ORM\ManyToMany(targetEntity: ActionConfig::class)]
    private Collection $actionConfigs;

    public function __construct(
        SkillName $name = SkillName::NULL,
        Collection $modifierConfigs = new ArrayCollection(),
        Collection $actionConfigs = new ArrayCollection(),
        ?SpawnEquipmentConfig $spawnEquipmentConfig = null,
        ?StatusConfig $specialistPointsConfig = null
    ) {
        $this->name = $name;
        $this->spawnEquipmentConfig = $spawnEquipmentConfig;
        $this->modifierConfigs = $modifierConfigs;
        $this->actionConfigs = $actionConfigs;
        $this->specialistPointsConfig = $specialistPointsConfig;
    }

    public function getName(): SkillName
    {
        return $this->name;
    }

    public function getNameAsString(): string
    {
        return $this->name->value;
    }

    public function update(self $skillConfig): void
    {
        $this->name = $skillConfig->name;
        $this->spawnEquipmentConfig = $skillConfig->spawnEquipmentConfig;
        $this->modifierConfigs = $skillConfig->modifierConfigs;
        $this->actionConfigs = $skillConfig->actionConfigs;
        $this->specialistPointsConfig = $skillConfig->specialistPointsConfig;
    }
}
