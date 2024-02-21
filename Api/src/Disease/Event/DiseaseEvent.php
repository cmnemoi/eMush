<?php

namespace Mush\Disease\Event;

use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Event\LoggableEventInterface;

class DiseaseEvent extends AbstractGameEvent implements LoggableEventInterface
{
    public const NEW_DISEASE = 'disease.new';
    public const APPEAR_DISEASE = 'disease.appear';
    public const TREAT_DISEASE = 'disease.treat';
    public const CURE_DISEASE = 'disease.cure';

    private PlayerDisease $playerDisease;
    private string $visibility = VisibilityEnum::PUBLIC;

    public function __construct(
        PlayerDisease $playerDisease,
        array $tags,
        \DateTime $time
    ) {
        $this->playerDisease = $playerDisease;

        parent::__construct($tags, $time);
    }

    public function getTargetPlayer(): Player
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

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
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
            'character_gender' => CharacterEnum::isMale($this->playerDisease->getPlayer()->getName()) ? 'male' : 'female',
        ];

        if (($author = $this->author) !== null) {
            $logParameters[$author->getLogKey()] = $author->getLogName();
        }

        return $logParameters;
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
