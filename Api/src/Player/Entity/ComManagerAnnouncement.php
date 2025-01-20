<?php

declare(strict_types=1);

namespace Mush\Player\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\MetaGame\Entity\SanctionEvidenceInterface;

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

    #[ORM\OneToMany(mappedBy: 'comManagerAnnouncement', targetEntity: Player::class)]
    private Collection $receivers;

    #[ORM\Column(type: 'text', nullable: false, options: ['default' => ''])]
    private string $announcement;

    public function __construct(Player $comManager, string $announcement)
    {
        $this->comManager = $comManager;
        $this->receivers = $this->comManager->getDaedalus()->getAlivePlayers();
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

    public function getReceivers(): Collection
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
