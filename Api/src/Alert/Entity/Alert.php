<?php

namespace Mush\Alert\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Alert\Entity\Collection\ReportedAlertCollection;
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

    /**
     * @ORM\OneToMany(targetEntity="Mush\Alert\Entity\ReportedAlert", mappedBy="alert")
     */
    private ?ReportedAlertCollection $reportedEvents = null;

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

    public function setReportedAlert(ReportedAlertCollection $reportedEvents): Alert
    {
        $this->reportedEvents = $reportedEvents;

        return $this;
    }

    public function getReportedEvent(): ?ReportedAlertCollection
    {
        return $this->reportedEvents;
    }

    public function addReportedAlert(ReportedAlert $reportedEvent): Alert
    {
        if ($this->reportedEvents === null) {
            $this->reportedEvents = new ReportedAlertCollection();
        }
        $this->reportedEvents->add($reportedEvent);

        return $this;
    }
}
