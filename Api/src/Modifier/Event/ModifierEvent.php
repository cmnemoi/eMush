<?php

namespace Mush\Modifier\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

class ModifierEvent extends AbstractGameEvent
{
    public const APPLY_MODIFIER = 'apply_modifier';
    public const CREATE_MODIFIER = 'create_modifier';
    public const DELETE_MODIFIER = 'delete_modifier';

    protected GameModifier $modifier;

    public function __construct(
        GameModifier $modifier,
        array $tags,
        \DateTime $time,
    ) {
        parent::__construct($tags, $time);

        $this->modifier = $modifier;

        $this->addTag($modifier->getModifierConfig()->getModifierName());
    }

    public function getModifier(): GameModifier
    {
        return $this->modifier;
    }

    public function getModifierHolder(): ModifierHolderInterface
    {
        return $this->modifier->getModifierHolder();
    }

    // to avoid infinite loops in eventService
    // EventModifier are not modifiable
    public function getModifiersByPriorities(array $priorities): ModifierCollection
    {
        return new ModifierCollection([]);
    }

    public function getLogParameters(): array
    {
        $modifierHolder = $this->modifier->getModifierHolder();
        $logParameters = [];

        $provider = $this->modifier->getModifierProvider();
        if ($provider instanceof LogParameterInterface) {
            $logParameters = array_merge($logParameters, [$provider->getLogKey() => $provider->getLogName()]);
        }

        switch (true) {
            case $modifierHolder instanceof Player:
                $place = $modifierHolder->getPlace();
                $logParameters[$place->getLogKey()] = $place->getLogName();
                $logParameters['target_' . $modifierHolder->getLogKey()] = $modifierHolder->getLogName();

                break;

            case $modifierHolder instanceof Place:
                $logParameters[$modifierHolder->getLogKey()] = $modifierHolder->getLogName();

                break;

            case $modifierHolder instanceof GameEquipment:
                $place = $modifierHolder->getPlace();
                $logParameters[$place->getLogKey()] = $place->getLogName();
                $logParameters['target_' . $modifierHolder->getLogKey()] = $modifierHolder->getLogName();

                break;

            default:
                return $logParameters;
        }

        return $logParameters;
    }
}
