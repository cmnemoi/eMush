<?php

namespace Mush\Alert\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Alert\Entity\Collection\AlertElementCollection;
use Mush\Daedalus\Entity\Daedalus;

/**
 * Class Alert.
 *
 * @ORM\Entity(repositoryClass="Mush\Alert\Repository\AlertRepository")
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

    /**
     * @ORM\OneToMany(targetEntity="Mush\Alert\Entity\AlertElement", mappedBy="alert")
     */
    private Collection $alertElements;

    public function __construct()
    {
        $this->alertElements = new AlertElementCollection();
    }

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

    public function getAlertElements(): AlertElementCollection
    {
        if (!$this->alertElements instanceof AlertElementCollection) {
            $this->alertElements = new AlertElementCollection($this->alertElements->toArray());
        }

        return $this->alertElements;
    }

    public function addAlertElement(AlertElement $alertElement): Alert
    {
        $this->alertElements->add($alertElement);
        $alertElement->setAlert($this);

        return $this;
    }
}
