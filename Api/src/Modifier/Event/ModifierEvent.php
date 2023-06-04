<?php

namespace Mush\Modifier\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolder;

class ModifierEvent extends AbstractGameEvent
{
    public const APPLY_MODIFIER = 'apply_modifier';
    public const CREATE_MODIFIER = 'create_modifier';
    public const DELETE_MODIFIER = 'delete_modifier';

    protected GameModifier $modifier;
    protected bool $wasModifierUsed;

    public function __construct(
        GameModifier $modifier,
        array $tags,
        \DateTime $time,
        bool $wasModifierUsed
    ) {
        parent::__construct($tags, $time);

        $this->modifier = $modifier;
        $this->wasModifierUsed = $wasModifierUsed;

        if (($name = $modifier->getModifierConfig()->getModifierName()) !== null) {
            $this->tags[] = $name;
        }
    }

    public function getModifier(): GameModifier
    {
        return $this->modifier;
    }

    public function getModifierHolder(): ModifierHolder
    {
        return $this->modifier->getModifierHolder();
    }

    public function wasModifierUsed(): bool
    {
        return $this->wasModifierUsed;
    }

    // to avoid infinite loops in eventService
    // EventModifier are not modifiable
    public function getModifiers(): ModifierCollection
    {
        return new ModifierCollection([]);
    }
}
