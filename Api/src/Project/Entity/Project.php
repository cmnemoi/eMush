<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\ActionTargetInterface;
use Mush\Action\Enum\ActionTargetName;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

#[ORM\Entity]
class Project implements LogParameterInterface, ActionTargetInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ProjectConfig::class)]
    private ProjectConfig $config;

    #[ORM\Column(type: 'integer', length: 255, nullable: false, options: ['default' => 0])]
    private int $progress = 0;

    #[ORM\ManyToOne(inversedBy: 'projects', targetEntity: Daedalus::class)]
    private Daedalus $daedalus;

    public function __construct(ProjectConfig $config, Daedalus $daedalus)
    {
        $this->config = $config;
        $this->daedalus = $daedalus;
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

    public function getMinEfficiency(): int
    {
        return $this->config->getEfficiency();
    }

    public function getMaxEfficiency(): int
    {
        return (int) ($this->getMinEfficiency() + $this->getMinEfficiency() / 2);
    }

    public function getBonusSkills(): array
    {
        return $this->config->getBonusSkills();
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function makeProgress(int $progress): void
    {
        $this->progress += $progress;
    }

    public function getClassName(): string
    {
        return self::class;
    }

    public function getLogName(): string
    {
        return $this->getName()->value;
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::PROJECT;
    }

    public function getActionTargetName(array $context): string
    {
        return ActionTargetName::PROJECT->value;
    }
}
