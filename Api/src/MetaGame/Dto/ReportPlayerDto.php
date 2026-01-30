<?php

namespace Mush\MetaGame\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ReportPlayerDto
{
    private int $playerId;

    private string $reason;

    #[Assert\NotNull]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(
        max: 4096,
        maxMessage: 'The message cannot be longer than {{ limit }} characters'
    )]
    private string $adminMessage;

    public function getPlayerId(): int
    {
        return $this->playerId;
    }

    public function setPlayerId(int $playerId): self
    {
        $this->playerId = $playerId;

        return $this;
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

    public function getAdminMessage(): string
    {
        return $this->adminMessage;
    }

    public function setAdminMessage(string $message): self
    {
        $this->adminMessage = $message;

        return $this;
    }
}
