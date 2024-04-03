<?php

namespace Mush\MetaGame\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\User\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: 'moderationSanction')]
class ModerationSanction
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'moderationSanctions')]
    private User $user;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $moderationAction;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $reason;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $message;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $isVisibleByUser = false;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $startDate;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $endDate;

    public function __construct(User $user, \DateTime $startDate)
    {
        $this->startDate = $startDate;
        $this->user = $user;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getModerationAction(): string
    {
        return $this->moderationAction;
    }

    public function setModerationAction(string $moderationAction): self
    {
        $this->moderationAction = $moderationAction;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getIsVisibleByUser(): bool
    {
        return $this->isVisibleByUser;
    }

    public function setIsVisibleByUser(bool $isVisibleByUser): self
    {
        $this->isVisibleByUser = $isVisibleByUser;

        return $this;
    }

    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getIsActive(): bool
    {
        $currentTime = new \DateTime();
        if ($this->endDate > $currentTime && $this->startDate < $currentTime) {
            return true;
        }

        return false;
    }
}
