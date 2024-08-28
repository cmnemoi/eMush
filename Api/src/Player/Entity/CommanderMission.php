<?php

declare(strict_types=1);

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\MetaGame\Entity\SanctionEvidenceInterface;

#[ORM\Entity]
class CommanderMission implements SanctionEvidenceInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'orderedMissions')]
    private Player $commander;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'receivedMissions')]
    private Player $subordinate;

    #[ORM\Column(type: 'text', nullable: false, options: ['default' => ''])]
    private string $mission;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $pending = true;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $completed = false;

    public function __construct(Player $commander, Player $subordinate, string $mission)
    {
        $this->commander = $commander;
        $this->subordinate = $subordinate;
        $this->mission = $mission;

        $this->subordinate->addReceivedMission($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCommander(): Player
    {
        return $this->commander;
    }

    public function getSubordinate(): Player
    {
        return $this->subordinate;
    }

    public function getMission(): string
    {
        return $this->mission;
    }

    public function getLanguage(): string
    {
        return $this->subordinate->getLanguage();
    }

    public function getCommanderName(): string
    {
        return $this->commander->getName();
    }

    public function getCreatedAtOrThrow(): \DateTime
    {
        return $this->createdAt ?? throw new \RuntimeException('Commander mission should have a creation date');
    }

    public function isPending(): bool
    {
        return $this->pending;
    }

    public function isNotPending(): bool
    {
        return $this->pending === false;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function accept(): self
    {
        $this->pending = false;

        return $this;
    }

    public function reject(): self
    {
        $this->pending = false;
        $this->completed = true;

        return $this;
    }

    public function toggleCompletion(): self
    {
        $this->completed = !$this->completed;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->mission;
    }

    public function getClassName(): string
    {
        return self::class;
    }

    public function getDay(): null
    {
        return null;
    }

    public function getCycle(): null
    {
        return null;
    }
}
