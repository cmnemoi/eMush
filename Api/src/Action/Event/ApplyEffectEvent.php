<?php

namespace Mush\Action\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Event\LoggableEventInterface;

class ApplyEffectEvent extends AbstractGameEvent implements LoggableEventInterface
{
    public const CONSUME = 'action.consume';
    public const HEAL = 'action.heal';
    public const REPORT_FIRE = 'report.fire';
    public const REPORT_EQUIPMENT = 'report.equipment';
    public const PLAYER_GET_SICK = 'player.get.sick';
    public const PLAYER_CURE_INJURY = 'player.cure.injury';
    public const ULTRA_HEAL = 'ultra.heal';

    private string $visibility;
    private ?LogParameterInterface $parameter;

    public function __construct(
        Player $player,
        ?LogParameterInterface $parameter,
        string $visibility,
        array $tags,
        \DateTime $time
    ) {
        $this->author = $player;
        $this->visibility = $visibility;
        $this->parameter = $parameter;

        parent::__construct($tags, $time);
    }

    public function getAuthor(): Player
    {
        $player = $this->author;
        if ($player === null) {
            throw new \Exception('applyEffectEvent should have a player');
        }

        return $player;
    }

    public function getPlace(): Place
    {
        return $this->getAuthor()->getPlace();
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getParameter(): ?LogParameterInterface
    {
        return $this->parameter;
    }

    public function getModifiers(): ModifierCollection
    {
        $modifiers = $this->getAuthor()->getAllModifiers()->getEventModifiers($this)->getTargetModifiers(false);

        $parameter = $this->parameter;
        if ($parameter instanceof ModifierHolderInterface) {
            $modifiers = $modifiers->addModifiers($parameter->getAllModifiers()->getEventModifiers($this)->getTargetModifiers(true));
        }

        return $modifiers;
    }

    public function getLogParameters(): array
    {
        $player = $this->getAuthor();
        $logParameters = [
            'character' => $player->getLogName(),
            'place' => $player->getPlace()->getName(),
        ];

        if (($actionParameter = $this->getParameter()) !== null) {
            'target_' . $logParameters[$actionParameter->getLogKey()] = $actionParameter->getLogName();
        }

        return $logParameters;
    }
}
