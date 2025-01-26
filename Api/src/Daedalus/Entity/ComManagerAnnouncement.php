<?php

declare(strict_types=1);

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\MetaGame\Entity\SanctionEvidenceInterface;
use Mush\Player\Entity\Player;

#[ORM\Entity]
class ComManagerAnnouncement implements SanctionEvidenceInterface
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    private Player $comManager;

    #[ORM\Column(type: 'text', nullable: false, options: ['default' => ''])]
    private string $announcement;

    #[ORM\ManyToOne(targetEntity: Daedalus::class, inversedBy: 'generalAnnouncements')]
    private Daedalus $daedalus;

    public function __construct(Player $comManager, string $announcement)
    {
        $this->comManager = $comManager;
        $this->announcement = $announcement;
        $this->daedalus = $this->comManager->getDaedalus();
        $this->daedalus->addGeneralAnnouncement($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getComManager(): Player
    {
        return $this->comManager;
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

    public function getDay(): int
    {
        return $this->daedalus->getDay();
    }

    public function getCycle(): int
    {
        return $this->daedalus->getCycle();
    }
}
