<?php

namespace Mush\Player\Entity\Dto;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Validator\FullDaedalus;
use Mush\Daedalus\Validator\StartingDaedalus;
use Mush\Player\Validator\UniqueCharacter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PlayerRequest.
 *
 * @UniqueCharacter
 */
class PlayerCreateRequest
{
    /**
     * @Assert\NotBlank
     */
    private ?string $character = null;
    /**
     * @Assert\NotNull
     * @StartingDaedalus
     * @FullDaedalus
     */
    private ?Daedalus $daedalus = null;

    public function getCharacter(): ?string
    {
        return $this->character;
    }

    public function setCharacter(?string $character): PlayerCreateRequest
    {
        $this->character = $character;

        return $this;
    }

    public function getDaedalus(): ?Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(?Daedalus $daedalus): PlayerCreateRequest
    {
        $this->daedalus = $daedalus;

        return $this;
    }
}
