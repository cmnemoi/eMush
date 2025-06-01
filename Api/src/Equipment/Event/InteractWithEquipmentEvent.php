<?php

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
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

    protected function getEventSpecificTargets(TriumphTarget $targetSetting, PlayerCollection $scopeTargets): PlayerCollection
    {
        return match ($targetSetting) {
            TriumphTarget::AUTHOR => $scopeTargets->filter(fn (Player $player) => $player === $this->getAuthor()),
            default => throw new \LogicException("Triumph target {$targetSetting->toString()} is not supported"),
        };
    }
}
