<?php

namespace Mush\Modifier\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

class EventCreationService implements EventCreationServiceInterface
{
    public function createEvents(
        AbstractEventConfig $eventConfig,
        ModifierHolder $modifierRange,
        ?Player $player,
        array $tags,
        \DateTime $time
    ): array {
        if ($eventConfig instanceof VariableEventConfig) {
            return $this->createVariableEvents($eventConfig, $tags, $time, $modifierRange);
        } else {
            throw new \Error('This eventConfig class is not supported');
        }
    }

    private function createVariableEvents(
        VariableEventConfig $eventConfig,
        array $tags,
        \DateTime $time,
        ModifierHolder $modifierRange,
    ): array {
        $variableHolderClass = $eventConfig->getVariableHolderClass();
        switch ($variableHolderClass) {
            case ModifierHolderClassEnum::DAEDALUS:
                $daedalus = $this->getDaedalusFromModifierHolder($modifierRange);

                return [$eventConfig->createEvent($tags, $time, $daedalus)];

            case ModifierHolderClassEnum::PLAYER:
                $players = $this->getPlayersFromModifierHolder($modifierRange);

                $events = [];
                foreach ($players as $player) {
                    $events[] = $eventConfig->createEvent($tags, $time, $player);
                }

                return $events;
            default:
                throw new \Error("This variableHolderClass {$variableHolderClass} is not supported");
        }
    }

    private function getDaedalusFromModifierHolder(ModifierHolder $modifierHolder): Daedalus
    {
        if ($modifierHolder instanceof Player) {
            return $modifierHolder->getDaedalus();
        }
        if ($modifierHolder instanceof Place) {
            return $modifierHolder->getDaedalus();
        }
        if ($modifierHolder instanceof GameEquipment) {
            return $modifierHolder->getDaedalus();
        }
        if ($modifierHolder instanceof Daedalus) {
            return $modifierHolder;
        }

        throw new \Error('unexpected ModifierHolder class');
    }

    private function getPlayersFromModifierHolder(ModifierHolder $modifierHolder): PlayerCollection
    {
        if ($modifierHolder instanceof Player) {
            return new PlayerCollection([$modifierHolder]);
        }
        if ($modifierHolder instanceof Place) {
            return $modifierHolder->getPlayers()->getPlayerAlive();
        }
        if ($modifierHolder instanceof Daedalus) {
            return $modifierHolder->getPlayers()->getPlayerAlive();
        }

        if ($modifierHolder instanceof GameEquipment) {
            $holder = $modifierHolder->getHolder();

            if ($holder instanceof Player) {
                return new PlayerCollection([$holder]);
            } else {
                throw new \Error('this equipment do not have a player holder');
            }
        }

        throw new \Error('unexpected ModifierHolder class');
    }
}
