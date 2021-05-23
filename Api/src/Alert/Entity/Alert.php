<?php

namespace Mush\Alert\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Daedalus\Entity\Daedalus;

/**
 * @ORM\Entity
 * @ORM\Table(name="alert")
 */
class Alert
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=255, nullable=false)
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Daedalus\Entity\Daedalus", inversedBy="alerts")
     */
    private Daedalus $daedalus;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Alert
    {
        $this->name = $name;

        return $this;
    }

    public function setDaedalus(Daedalus $daedalus): Alert
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
