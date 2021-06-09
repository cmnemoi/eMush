<?php

namespace Mush\Disease\Event;

use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\Event;

class DiseaseEvent extends Event
{
    public const NEW_DISEASE = 'disease.new';
    public const APPEAR_DISEASE = 'disease.appear';
    public const TREAT_DISEASE = 'disease.treat';
    public const CURE_DISEASE = 'disease.cure';

    private ?Player $author;
    private PlayerDisease $playerDisease;
    private \DateTime $time;
    private string $cureReason;

    public function __construct(PlayerDisease $playerDisease, \DateTime $time)
    {
        $this->playerDisease = $playerDisease;
        $this->time = $time;
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

    public function getDiseaseConfig(): DiseaseConfig
    {
        return $this->playerDisease->getDiseaseConfig();
    }

    public function getPlayerDisease(): PlayerDisease
    {
        return $this->playerDisease;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }

    public function getCureReason(): string
    {
        return $this->cureReason;
    }

    public function setCureReason(string $cureReason): DiseaseEvent
    {
        $this->cureReason = $cureReason;

        return $this;
    }
}
