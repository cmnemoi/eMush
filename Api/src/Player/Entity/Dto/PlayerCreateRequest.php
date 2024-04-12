<?php

namespace Mush\Player\Entity\Dto;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Validator\FullDaedalus;
use Mush\Daedalus\Validator\StartingDaedalus;
use Mush\Player\Validator\UniqueCharacter;
use Mush\User\Entity\User;
use Mush\User\Validator\UniqueUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PlayerRequest.
 *
 * @UniqueCharacter
 *
 * @UniqueUser
 */
class PlayerCreateRequest
{
    /**
     * @Assert\NotBlank
     */
    private ?string $character = null;

    /**
     * @Assert\NotNull
     *
     * @StartingDaedalus
     *
     * @FullDaedalus
     */
    private ?Daedalus $daedalus = null;

    /**
     * @Assert\NotNull
     */
    private ?User $user = null;

    public function getCharacter(): ?string
    {
        return $this->character;
    }

    public function setCharacter(?string $character): self
    {
        $this->character = $character;

        return $this;
    }

    public function getDaedalus(): ?Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(?Daedalus $daedalus): self
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
