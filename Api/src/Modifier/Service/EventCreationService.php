<?php

namespace Mush\Modifier\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Event\SymptomEvent;
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
        ModifierHolder $modifierHolder,
        array $tags,
        \DateTime $time,
        bool $reverse = false
    ): array {
        if ($reverse) {
            $eventConfig = $eventConfig->revertEvent();
        }

        if ($eventConfig instanceof VariableEventConfig) {
            return $this->createVariableEvents($eventConfig, $tags, $time, $modifierHolder);
        } elseif ($eventConfig instanceof SymptomConfig) {
            return $this->createSymptomEvents($eventConfig, $tags, $time, $modifierHolder);
        } else {
            $className = $eventConfig::class;
            throw new \Exception("This eventConfig ({$className}) class is not supported");
        }
    }

    private function createVariableEvents(
        VariableEventConfig $eventConfig,
        array $tags,
        \DateTime $time,
        ModifierHolder $modifierHolder,
    ): array {
        $variableHolderClass = $eventConfig->getVariableHolderClass();
        switch ($variableHolderClass) {
            case ModifierHolderClassEnum::DAEDALUS:
                $daedalus = $this->getDaedalusFromModifierHolder($modifierHolder);

                return [$eventConfig->createEvent($tags, $time, $daedalus)];

            case ModifierHolderClassEnum::PLAYER:
                $players = $this->getPlayersFromModifierHolder($modifierHolder);

                $events = [];
                foreach ($players as $player) {
                    $events[] = $eventConfig->createEvent($tags, $time, $player);
                }

                return $events;
            default:
                throw new \Exception("This variableHolderClass {$variableHolderClass} is not supported");
        }
    }

    private function createSymptomEvents(
        SymptomConfig $eventConfig,
        array $tags,
        \DateTime $time,
        ModifierHolder $modifierHolder,
    ): array {
        if (!($modifierHolder instanceof Player)) {
            throw new \Exception("symptom config ({$eventConfig->getName()}) should have a PLAYER range");
        }

        $symptomEvent = new SymptomEvent($eventConfig->getEventName(), $modifierHolder, $tags, $time);
        $symptomEvent->setVisibility($eventConfig->getVisibility());

        return [$symptomEvent];
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

        $className = $modifierHolder::class;
        throw new \Exception("This eventConfig ({$className}) class is not supported");
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
                throw new \Exception("this equipment ({$modifierHolder->getName()}) do not have a player holder");
            }
        }

        $className = $modifierHolder::class;
        throw new \Exception("This eventConfig ({$className}) class is not supported");
    }
}
