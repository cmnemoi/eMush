<?php

namespace Mush\Player\Entity\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PlayerEndRequest
{
    /**
     * @Assert\NotBlank
     */
    private ?string $message = null;

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): PlayerEndRequest
    {
        $this->message = $message;

        return $this;
    }
}
