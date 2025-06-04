<?php

namespace Mush\Hunter\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Hunter\Entity\Hunter;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;
use Mush\Triumph\Enum\TriumphTarget;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

class HunterEvent extends AbstractGameEvent implements LoggableEventInterface, TriumphSourceEventInterface
{
    use TriumphSourceEventTrait;

    public const ASTEROID_DESTRUCTION = 'asteroid.destruction';
    public const HUNTER_DEATH = 'hunter.death';
    public const HUNTER_SHOT = 'hunter.shot';

    protected Hunter $hunter;
    protected string $visibility;

    public function __construct(Hunter $hunter, string $visibility, array $tags, \DateTime $time)
    {
        parent::__construct($tags, $time);

        $this->hunter = $hunter;
        $this->visibility = $visibility;
    }

    public function getHunter(): Hunter
    {
        return $this->hunter;
    }

    public function getPlace(): Place
    {
        return $this->hunter->getPlace();
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getLogParameters(): array
    {
        return [
            $this->hunter->getLogKey() => $this->hunter->getLogName(),
        ];
    }

    public function getDaedalus(): Daedalus
    {
        return $this->hunter->getDaedalus();
    }

    protected function getEventSpecificTargets(TriumphTarget $targetSetting, PlayerCollection $scopeTargets): PlayerCollection
    {
        return match ($targetSetting) {
            TriumphTarget::AUTHOR => $scopeTargets->filter(fn (Player $player) => $player === $this->getAuthor()),
            default => throw new \LogicException("Triumph target {$targetSetting->toString()} is not supported"),
        };
    }
}
