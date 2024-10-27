<?php

declare(strict_types=1);

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\MetaGame\Entity\SanctionEvidenceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;

#[ORM\Entity]
class ComManagerAnnouncement implements SanctionEvidenceInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Player::class, inversedBy: 'createdAnnouncements')]
    private Player $comManager;

    #[ORM\ManyToOne(targetEntity: PlayerCollection::class, inversedBy: 'receivedAnnouncements')]
    private Player $receivers;

    #[ORM\Column(type: 'text', nullable: false, options: ['default' => ''])]
    private string $announcement;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $pending = true;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $completed = false;

    public function __construct(Player $comManager, string $announcement)
    {
        $this->comManager = $comManager;
        $this->receivers = $this->comManager->getDaedalus()->getAlivePlayersWithMeansOfCommunication();
        $this->announcement = $announcement;

        foreach ($this->receivers as $receiver) {
            $receiver->addReceivedAnnouncement($this);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getComManager(): Player
    {
        return $this->comManager;
    }

    public function getReceivers(): PlayerCollection
    {
        return $this->receivers;
    }

    public function getAnnouncement(): string
    {
        return $this->announcement;
    }

    public function getLanguage(): string
    {
        return $this->comManager->getLanguage();
    }

    public function getComManagerName(): string
    {
        return $this->comManager->getName();
    }

    public function getCreatedAtOrThrow(): \DateTime
    {
        return $this->createdAt ?? throw new \RuntimeException('Comms Manager Announcement should have a creation date');
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
        return $this->announcement;
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
