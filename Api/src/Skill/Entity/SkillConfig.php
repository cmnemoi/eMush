<?php

declare(strict_types=1);

namespace Mush\Skill\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\ActionConfig;
use Mush\Equipment\Entity\Config\SpawnEquipmentConfig;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Skill\Dto\SkillConfigDto;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\ConfigData\StatusConfigData;
use Mush\Status\Entity\Config\ChargeStatusConfig;

#[ORM\Entity]
class SkillConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: SkillEnum::class, options: ['default' => SkillEnum::NULL])]
    private SkillEnum $name;

    #[ORM\ManyToOne(targetEntity: SpawnEquipmentConfig::class, cascade: ['persist'])]
    private ?SpawnEquipmentConfig $spawnEquipmentConfig;

    #[ORM\ManyToOne(targetEntity: ChargeStatusConfig::class, cascade: ['persist'])]
    private ?ChargeStatusConfig $skillPointsConfig;

    #[ORM\ManyToMany(targetEntity: AbstractModifierConfig::class)]
    private Collection $modifierConfigs;

    #[ORM\ManyToMany(targetEntity: ActionConfig::class)]
    private Collection $actionConfigs;

    public function __construct(
        SkillEnum $name = SkillEnum::NULL,
        ArrayCollection $modifierConfigs = new ArrayCollection(),
        ArrayCollection $actionConfigs = new ArrayCollection(),
        ?SpawnEquipmentConfig $spawnEquipmentConfig = null,
        ?ChargeStatusConfig $skillPointsConfig = null
    ) {
        $this->name = $name;
        $this->spawnEquipmentConfig = $spawnEquipmentConfig;
        $this->modifierConfigs = $modifierConfigs;
        $this->actionConfigs = $actionConfigs;
        $this->skillPointsConfig = $skillPointsConfig;
    }

    public static function createFromDto(SkillConfigDto $dto): self
    {
        return new self(
            name: $dto->name,
            skillPointsConfig: $dto->skillPointsConfig !== null ? ChargeStatusConfig::fromConfigData(
                StatusConfigData::getByName($dto->skillPointsConfig->toString())
            ) : null,
        );
    }

    public function getName(): SkillEnum
    {
        return $this->name;
    }

    public function getNameAsString(): string
    {
        return $this->name->value;
    }

    public function getModifierConfigs(): ArrayCollection
    {
        return new ArrayCollection($this->modifierConfigs->toArray());
    }

    /**
     * @return ArrayCollection<int, ActionConfig>
     */
    public function getActionConfigs(): ArrayCollection
    {
        return new ArrayCollection($this->actionConfigs->toArray());
    }

    public function getSkillPointsConfig(): ChargeStatusConfig
    {
        return $this->skillPointsConfig ?? ChargeStatusConfig::createNull();
    }

    public function update(self $skillConfig): void
    {
        $this->name = $skillConfig->name;
        $this->spawnEquipmentConfig = $skillConfig->spawnEquipmentConfig;
        $this->modifierConfigs = $skillConfig->modifierConfigs;
        $this->actionConfigs = $skillConfig->actionConfigs;
        $this->skillPointsConfig = $skillConfig->skillPointsConfig;
    }
}
