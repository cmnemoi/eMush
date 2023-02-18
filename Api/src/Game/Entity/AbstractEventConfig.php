<?php

namespace Mush\Game\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class storing the various information needed to create events.
 */
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'variable_event_config' => VariableEventConfig::class,
])]
abstract class AbstractEventConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    protected int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    protected string $name;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    protected string $eventName;

    public function getId(): ?int
    {
        return $this->id;
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

    public function reverseEvent(): ?AbstractEventConfig
    {
        return null;
    }
}
