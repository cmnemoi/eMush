<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class ExplorationLog
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Version]
    #[ORM\Column(type: 'integer', length: 255, nullable: false, options: ['default' => 1])]
    private int $version = 1;

    #[ORM\ManyToOne(targetEntity: ClosedExploration::class, inversedBy: 'logs')]
    private ClosedExploration $closedExploration;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $planetSectorName = '';

    #[ORM\Column(type: 'string', nullable: false)]
    private string $eventName = '';

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

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
