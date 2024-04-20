<?php

declare(strict_types=1);

namespace Mush\Project\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Action\Entity\ActionTargetInterface;
use Mush\Action\Enum\ActionTargetName;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Project\Enum\ProjectType;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;

#[ORM\Entity]
class Project implements LogParameterInterface, ActionTargetInterface, StatusHolderInterface
{
    use TargetStatusTrait;

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

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: StatusTarget::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $statuses;

    public function __construct(ProjectConfig $config, Daedalus $daedalus)
    {
        $this->config = $config;
        $this->daedalus = $daedalus;
        $this->statuses = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->config->getName()->value;
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
        return $this->getName();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::PROJECT;
    }

    public function getActionTargetName(array $context): string
    {
        return ActionTargetName::PROJECT->value;
    }

    public function addStatus(Status $status): static
    {
        if (!$this->getStatuses()->contains($status)) {
            if (!$statusTarget = $status->getStatusTargetTarget()) {
                $statusTarget = new StatusTarget();
            }
            $statusTarget->setOwner($status);
            $statusTarget->setProject($this);
            $this->statuses->add($statusTarget);
        }

        return $this;
    }
}
