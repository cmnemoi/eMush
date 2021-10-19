<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Neron
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\OneToOne(targetEntity="Mush\Daedalus\Entity\Daedalus", mappedBy="neron")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $isInhibited = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): self
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function setIsInhibited(bool $isInhibited): self
    {
        $this->isInhibited = $isInhibited;

        return $this;
    }

    public function isInhibited(): bool
    {
        return $this->isInhibited;
    }
}
