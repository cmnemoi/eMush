<?php

namespace Mush\Player\Entity\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PlayerEndRequest
{
    /**
     * @Assert\Type("string")
     *
     * @Assert\Length(max = 300)
     */
    private ?string $message = null;

    private array $likedPlayers = [];

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getLikedPlayers(): array
    {
        return $this->likedPlayers;
    }

    public function setLikedPlayers(array $likedPlayers): self
    {
        $this->likedPlayers = $likedPlayers;

        return $this;
    }
}
