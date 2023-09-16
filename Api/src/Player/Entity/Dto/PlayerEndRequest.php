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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
