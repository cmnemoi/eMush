<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Triumph\Enum\TriumphTarget;

class InteractWithEquipmentEvent extends EquipmentEvent
{
    public function __construct(
        GameEquipment $equipment,
        ?Player $author,
        string $visibility,
        array $tags,
        \DateTime $time
    ) {
        parent::__construct($equipment, false, $visibility, $tags, $time);

        $this->author = $author;
    }

    public function getLogParameters(): array
    {
        $logParameters = [];

        $logParameters['target_' . $this->getGameEquipment()->getLogKey()] = $this->getGameEquipment()->getLogName();

        if ($this->author instanceof Player) {
            $logParameters[$this->author->getLogKey()] = $this->author->getLogName();
        }

        return $logParameters;
    }

    protected function getEventSpecificTargets(string $targetSetting, PlayerCollection $scopeTargets): PlayerCollection
    {
        return match ($targetSetting) {
            TriumphTarget::AUTHOR->toString() => $scopeTargets->filter(fn (Player $player) => $player === $this->getAuthor()),
            TriumphTarget::AUTHOR_CHAO->toString() => $scopeTargets->filter(fn (Player $player) => $player === $this->getAuthor() && $player->getName() === CharacterEnum::CHAO),
            default => throw new \LogicException("Triumph target {$targetSetting} is not supported"),
        };
    }
}
