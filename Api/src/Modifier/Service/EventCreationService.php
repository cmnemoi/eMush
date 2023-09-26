<?php

namespace Mush\Modifier\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\AbstractEventConfig;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

class EventCreationService implements EventCreationServiceInterface
{
    public function createEvents(
        AbstractEventConfig $eventConfig,
        ModifierHolderInterface $modifierRange,
        int $priority,
        array $tags,
        \DateTime $time,
        bool $reverse = false
    ): EventChain {
        if ($reverse) {
            $eventConfig = $eventConfig->revertEvent();
        }

        if ($eventConfig instanceof VariableEventConfig) {
            return $this->createVariableEvents($eventConfig, $priority, $tags, $time, $modifierRange);
        } else {
            $className = $eventConfig::class;
            throw new \Exception("This eventConfig ({$className}) class is not supported");
        }
    }

    private function createVariableEvents(
        VariableEventConfig $eventConfig,
        int $priority,
        array $tags,
        \DateTime $time,
        ModifierHolderInterface $modifierRange,
    ): EventChain {
        $variableHolderClass = $eventConfig->getVariableHolderClass();
        switch ($variableHolderClass) {
            case ModifierHolderClassEnum::DAEDALUS:
                $daedalus = $this->getDaedalusFromModifierHolder($modifierRange);

                return new EventChain([$eventConfig->createEvent($priority, $tags, $time, $daedalus)]);

            case ModifierHolderClassEnum::PLAYER:
                $players = $this->getPlayersFromModifierHolder($modifierRange);

                $events = [];
                foreach ($players as $player) {
                    $events[] = $eventConfig->createEvent($priority, $tags, $time, $player);
                }

                return new EventChain($events);
            default:
                throw new \Exception("This variableHolderClass {$variableHolderClass} is not supported");
        }
    }

    private function getDaedalusFromModifierHolder(ModifierHolderInterface $modifierHolder): Daedalus
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

        $className = $modifierHolder::class;
        throw new \Exception("This eventConfig ({$className}) class is not supported");
    }

    private function getPlayersFromModifierHolder(ModifierHolderInterface $modifierHolder): PlayerCollection
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
                throw new \Exception("this equipment ({$modifierHolder->getName()}) do not have a player holder");
            }
        }

        $className = $modifierHolder::class;
        throw new \Exception("This eventConfig ({$className}) class is not supported");
    }
}
