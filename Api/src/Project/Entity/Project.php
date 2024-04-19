<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;

#[ORM\Entity]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ProjectConfig::class)]
    private ProjectConfig $config;

    public function __construct(ProjectConfig $config)
    {
        $this->config = $config;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ProjectName
    {
        return $this->config->getName();
    }

    public function getType(): ProjectType
    {
        return $this->config->getType();
    }

    public function getEfficiency(): int
    {
        return $this->config->getEfficiency();
    }

    public function getBonusSkills(): array
    {
        return $this->config->getBonusSkills();
    }
}
