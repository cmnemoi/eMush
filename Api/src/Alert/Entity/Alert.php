<?php

namespace Mush\Alert\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\Alert\Entity\Collection\ReportedAlertCollection;
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
    private int $id;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\ManyToOne(targetEntity="Mush\Daedalus\Entity\Daedalus", inversedBy="alerts")
     */
    private Daedalus $daedalus;

    /**
     * @ORM\OneToMany(targetEntity="Mush\Alert\Entity\ReportedAlert", mappedBy="alert")
     */
    private ?Collection $reportedEvents = null;

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

    public function getReportedEvent(): ?ReportedAlertCollection
    {
        if (
            !$this->reportedEvents instanceof ReportedAlertCollection &&
            $this->reportedEvents !== null
        ) {
            $this->reportedEvents = new ReportedAlertCollection($this->reportedEvents->toArray());
        }

        return $this->reportedEvents;
    }

    public function addReportedAlert(ReportedAlert $reportedEvent): Alert
    {
        if ($this->reportedEvents === null) {
            $this->reportedEvents = new ReportedAlertCollection();
        }
        $this->reportedEvents->add($reportedEvent);
        $reportedEvent->setAlert($this);

        return $this;
    }
}
