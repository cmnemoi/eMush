<?php

namespace Mush\Disease\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Player\Entity\Player;

class SymptomEvent extends AbstractGameEvent
{
    public const TRIGGER_SYMPTOM = 'trigger.symptom';

    private Player $player;
    private string $symptomName;

    public function __construct(
        Player $player,
        string $symptomName,
        array $tags,
        \DateTime $time
    ) {
        $this->player = $player;
        $this->symptomName = $symptomName;

        parent::__construct($tags, $time);
    }

    public function getTargetPlayer(): Player
    {
        return $this->player;
    }

    public function getSymptomName(): string
    {
        return $this->symptomName;
    }

    public function getModifiers(): ModifierCollection
    {
        $modifiers = $this->getTargetPlayer()->getAllModifiers()->getEventModifiers($this)->getTargetModifiers(true);

        $author = $this->author;
        if ($author !== null) {
            $modifiers = $modifiers->addModifiers($author->getAllModifiers()->getEventModifiers($this)->getTargetModifiers(false));
        }

        return $modifiers;
    }
}
