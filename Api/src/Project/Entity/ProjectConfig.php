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

    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: ProjectName::class)]
    private ProjectName $name;

    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: ProjectType::class)]
    private ProjectType $type;

    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $efficiency;

    #[ORM\Column(type: 'array', nullable: false)]
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
}
