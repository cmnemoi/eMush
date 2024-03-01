<?php

namespace Mush\Modifier\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

class EventCreationService implements EventCreationServiceInterface
{
    public function getEventTargetsFromModifierHolder(
        string $eventTarget,
        ModifierHolderInterface $holder,
    ): array {
        switch ($eventTarget) {
            case ModifierHolderClassEnum::DAEDALUS:
                $daedalus = $this->getDaedalusFromModifierHolder($holder);

                return [$daedalus];
            case ModifierHolderClassEnum::PLAYER:
                return $this->getPlayersFromModifierHolder($holder)->toArray();

            default:
                throw new \Exception("This variableHolderClass {$eventTarget} is not supported");
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
