<?php

namespace Mush\Disease\Event;

use Mush\Action\Enum\ActionTypeEnum;
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

        // all symptoms are super dirty : dirty status application cannot be prevented by stainproof apron
        $tags[] = ActionTypeEnum::ACTION_SUPER_DIRTY;

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

    public function getModifiersByPriorities(array $priorities): ModifierCollection
    {
        $modifiers = $this->getTargetPlayer()->getAllModifiers()->getEventModifiers($this, $priorities)->getTargetModifiers(true);

        $author = $this->author;
        if ($author !== null) {
            $modifiers = $modifiers->addModifiers($author->getAllModifiers()->getEventModifiers($this, $priorities)->getTargetModifiers(false));
        }

        return $modifiers;
    }
}
