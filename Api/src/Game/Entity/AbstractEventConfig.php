<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Disease\Entity\Config\SymptomConfig;

/**
 * Class storing the various information needed to create events.
 */
#[ORM\Entity]
#[ORM\Table(name: 'event_config')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'variable_event_config' => VariableEventConfig::class,
    'symptom_config' => SymptomConfig::class,
])]
abstract class AbstractEventConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    protected int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    protected string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    protected string $eventName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function setEventName(string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function revertEvent(): ?AbstractEventConfig
    {
        return null;
    }
}
