<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Doctrine\ORM\Mapping as ORM;
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

    public function __construct(
        ProjectName $name,
        ProjectType $type,
        int $efficiency,
        array $bonusSkills,
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->efficiency = $efficiency;
        $this->bonusSkills = $bonusSkills;
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

    public function updateFromConfigData(array $configData): void
    {
        $this->name = $configData['name'];
        $this->type = $configData['type'];
        $this->efficiency = $configData['efficiency'];
        $this->bonusSkills = $configData['bonusSkills'];
    }
}
