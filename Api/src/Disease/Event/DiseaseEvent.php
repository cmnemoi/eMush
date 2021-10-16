<?php

namespace Mush\Disease\Event;

use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Event\LoggableEventInterface;

class DiseaseEvent extends AbstractGameEvent implements LoggableEventInterface
{
    public const NEW_DISEASE = 'disease.new';
    public const APPEAR_DISEASE = 'disease.appear';
    public const TREAT_DISEASE = 'disease.treat';
    public const CURE_DISEASE = 'disease.cure';

    private ?Player $author = null;
    private PlayerDisease $playerDisease;
    private string $visibility = VisibilityEnum::PUBLIC;

    public function __construct(
        PlayerDisease $playerDisease,
        string $cureReason,
        \DateTime $time
    ) {
        $this->playerDisease = $playerDisease;

        parent::__construct($cureReason, $time);
    }

    public function getAuthor(): ?Player
    {
        return $this->author;
    }

    public function setAuthor(?Player $author): DiseaseEvent
    {
        $this->author = $author;

        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->playerDisease->getPlayer();
    }

    public function getPlace(): Place
    {
        return $this->playerDisease->getPlayer()->getPlace();
    }

    public function getDiseaseConfig(): DiseaseConfig
    {
        return $this->playerDisease->getDiseaseConfig();
    }

    public function getPlayerDisease(): PlayerDisease
    {
        return $this->playerDisease;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getLogParameters(): array
    {
        $logParameters = [
            $this->getPlayerDisease()->getDiseaseConfig()->getLogKey() => $this->getPlayerDisease()->getDiseaseConfig()->getLogName(),
            'target_' . $this->playerDisease->getPlayer()->getLogKey() => $this->playerDisease->getPlayer()->getLogName(),
        ];

        if (($author = $this->author) !== null) {
            $logParameters[$author->getLogKey()] = $author->getLogName();
        }

        return $logParameters;
    }
}
