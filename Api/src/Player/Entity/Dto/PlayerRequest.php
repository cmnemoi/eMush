<?php


namespace Mush\Player\Entity\Dto;

use Mush\Daedalus\Entity\Daedalus;
use Symfony\Component\Validator\Constraints as Assert;
use Mush\Player\Validator\UniqueCharacter;

/**
 * Class PlayerRequest
 * @package Mush\Player\Entity\Dto
 * @UniqueCharacter
 */
class PlayerRequest
{
    /**
     * @Assert\NotBlank
     */
    private ?string $character = null;
    /**
     * @Assert\NotNull
     */
    private ?Daedalus $daedalus = null;

    public function getCharacter(): ?string
    {
        return $this->character;
    }

    public function setCharacter(?string $character): PlayerRequest
    {
        $this->character = $character;
        return $this;
    }

    public function getDaedalus(): ?Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(?Daedalus $daedalus): PlayerRequest
    {
        $this->daedalus = $daedalus;
        return $this;
    }
}