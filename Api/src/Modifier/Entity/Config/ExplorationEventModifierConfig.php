<?php

declare(strict_types=1);

namespace Mush\Modifier\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Modifier\Dto\ExplorationEventModifierConfigDto;

/**
 * Class storing the various information needed to modify the events that can be selected in exploration.
 * eventToRemove is used to select an event to be removed from the pool
 * eventToAdd is used to add an event to the pool.
 * weight is used to set the weight of the event added. Leave null if you replace an event and want to keep the original weight
 * action determine is you add, remove or replace an event
 * criteria determine if the event replaced or removed is selected based on the name(key) or the event_name.
 */
#[ORM\Entity]
class ExplorationEventModifierConfig extends EventModifierConfig
{
    public const string REMOVE = 'remove';
    public const string ADD = 'add';
    public const string REPLACE = 'replace';
    public const string EVENT_NAME = 'event_name';
    public const string NAME = 'name';

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $eventToRemove;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $eventToAdd;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $weight;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => 'replace'])]
    private string $action = 'replace';

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => 'event_name'])]
    private string $criteria = 'event_name';

    public static function fromDtoChild(ExplorationEventModifierConfigDto $eventModifierConfigDto, ?self $config = null): self
    {
        if ($config === null) {
            $config = new self($eventModifierConfigDto->key);
        }

        $config->setModifierName($eventModifierConfigDto->name)
            ->setModifierStrategy($eventModifierConfigDto->strategy)
            ->setModifierRange($eventModifierConfigDto->modifierRange);

        $config->setTargetEvent($eventModifierConfigDto->targetEvent)
            ->setPriority($eventModifierConfigDto->priority)
            ->setTagConstraints($eventModifierConfigDto->tagConstraints)
            ->setApplyWhenTargeted($eventModifierConfigDto->applyWhenTargeted);

        $config->setEventToRemove($eventModifierConfigDto->eventToRemove)
            ->setEventToAdd($eventModifierConfigDto->eventToAdd)
            ->setweight($eventModifierConfigDto->weight)
            ->setAction($eventModifierConfigDto->action)
            ->setCriteria($eventModifierConfigDto->criteria);

        return $config;
    }

    public function setEventToRemove(?string $eventToRemove): static
    {
        $this->eventToRemove = $eventToRemove;

        return $this;
    }

    public function getEventToRemove(): ?string
    {
        return $this->eventToRemove;
    }

    public function setEventToAdd(?string $eventToAdd): static
    {
        $this->eventToAdd = $eventToAdd;

        return $this;
    }

    public function getEventToAdd(): ?string
    {
        return $this->eventToAdd;
    }

    public function setweight(?int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getweight(): ?int
    {
        return $this->weight;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setCriteria(string $criteria): static
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getCriteria(): string
    {
        return $this->criteria;
    }
}
