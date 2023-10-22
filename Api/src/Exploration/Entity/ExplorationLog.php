<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
final class ExplorationLog
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: ClosedExploration::class, inversedBy: 'logs')]
    private ClosedExploration $closedExploration;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $planetSectorName = '';

    #[ORM\Column(type: 'string', nullable: false)]
    private string $eventName = '';

    #[ORM\Column(type: 'string', nullable: false)]
    private string $eventDescription = '';

    #[ORM\Column(type: 'string', nullable: false)]
    private string $eventOutcome = '';

    #[ORM\Column(type: 'array', nullable: false)]
    private array $parameters = [];

    public function __construct(ClosedExploration $closedExploration)
    {
        $this->closedExploration = $closedExploration;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getClosedExploration(): ClosedExploration
    {
        return $this->closedExploration;
    }

    public function getPlanetSectorName(): string
    {
        return $this->planetSectorName;
    }

    public function setPlanetSectorName(string $planetSectorName): void
    {
        $this->planetSectorName = $planetSectorName;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): void
    {
        $this->eventName = $eventName;
    }

    public function getEventDescription(): string
    {
        return $this->eventDescription;
    }

    public function setEventDescription(string $eventDescription): void
    {
        $this->eventDescription = $eventDescription;
    }

    public function getEventOutcome(): string
    {
        return $this->eventOutcome;
    }

    public function setEventOutcome(string $eventOutcome): void
    {
        $this->eventOutcome = $eventOutcome;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function addParameter(string $key, $value): void
    {
        $this->parameters[$key] = $value;
    }
}
