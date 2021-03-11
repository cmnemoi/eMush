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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): Neron
    {
        $this->daedalus = $daedalus;

        return $this;
    }
}
