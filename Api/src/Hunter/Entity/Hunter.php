<?php

namespace Mush\Hunter\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Hunter\Enum\HunterTargetEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Entity\StatusTarget;
use Mush\Status\Entity\TargetStatusTrait;

#[ORM\Entity]
#[ORM\Table(name: 'hunter')]
class Hunter implements StatusHolderInterface, LogParameterInterface
{
    use TargetStatusTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: HunterConfig::class)]
    private HunterConfig $hunterConfig;

    #[ORM\ManyToOne(targetEntity: Daedalus::class, inversedBy: 'hunters')]
    private Daedalus $daedalus;

    #[ORM\Column(type: 'integer')]
    private int $health;

    #[ORM\OneToMany(mappedBy: 'hunter', targetEntity: StatusTarget::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $statuses;

    #[ORM\Column(type: 'integer')]
    private int $armor;

    #[ORM\Column(type: 'string')]
    private string $target = HunterTargetEnum::DAEDALUS;

    #[ORM\Column(type: 'boolean')]
    private bool $inPool = true;

    public function __construct(HunterConfig $hunterConfig, Daedalus $daedalus)
    {
        $this->armor = $hunterConfig->getInitialArmor();
        $this->daedalus = $daedalus;
        $this->health = $hunterConfig->getInitialHealth();
        $this->hunterConfig = $hunterConfig;
        $this->statuses = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHunterConfig(): HunterConfig
    {
        return $this->hunterConfig;
    }

    public function setHunterConfig(HunterConfig $hunterConfig): self
    {
        $this->hunterConfig = $hunterConfig;

        return $this;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): self
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getHealth(): int
    {
        return $this->health;
    }

    public function setHealth(int $health): self
    {
        $this->health = $health;

        return $this;
    }

    public function getArmor(): int
    {
        return $this->armor;
    }

    public function setArmor(int $armor): self
    {
        $this->armor = $armor;

        return $this;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function isInPool(): bool
    {
        return $this->inPool;
    }

    public function putInPool(): self
    {
        $this->inPool = true;

        return $this;
    }

    public function unpool(): self
    {
        $this->inPool = false;

        return $this;
    }

    public function addStatus(Status $status): static
    {
        if (!$this->getStatuses()->contains($status)) {
            if (!$statusTarget = $status->getStatusTargetTarget()) {
                $statusTarget = new StatusTarget();
            }
            $statusTarget->setOwner($status);
            $statusTarget->setHunter($this);
            $this->statuses->add($statusTarget);
        }

        return $this;
    }

    public function getClassName(): string
    {
        return get_class($this);
    }

    public function getName(): string
    {
        return $this->getHunterConfig()->getHunterName();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::HUNTER;
    }

    public function getLogName(): string
    {
        return $this->getName();
    }

    public function getGameEquipment(): null
    {
        return null;
    }

    public function getPlace(): null
    {
        return null;
    }

    public function getPlayer(): null
    {
        return null;
    }
}
