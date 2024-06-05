<?php

namespace Mush\Modifier\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Game\Enum\GameVariableHolderEnum;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

final class EventCreationService implements EventCreationServiceInterface
{
    public function __construct(
        private GameEquipmentRepositoryInterface $gameEquipmentRepository
    ) {}

    public function getEventTargetsFromModifierHolder(
        string $eventTarget,
        ModifierHolderInterface $holder,
    ): array {
        switch ($eventTarget) {
            case GameVariableHolderEnum::DAEDALUS->value:
                $daedalus = $holder->getDaedalus();

                return [$daedalus];

            case GameVariableHolderEnum::PLAYER->value:
                return $this->getPlayersFromModifierHolder($holder)->toArray();

            case ModifierHolderClassEnum::EQUIPMENT:
                return $this->getEquipmentsFromModifierHolder($holder);

            case GameVariableHolderEnum::PROJECT->value:
                return $holder instanceof Daedalus ? $holder->getAllAvailableProjects()->toArray() : [];

            default:
                throw new \Exception("This variableHolderClass {$eventTarget} is not supported");
        }
    }

    private function getPlayersFromModifierHolder(
        ModifierHolderInterface $modifierHolder,
    ): PlayerCollection {
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
            }

            return new PlayerCollection([]);
        }

        $className = $modifierHolder::class;

        throw new \Exception("This eventConfig ({$className}) class is not supported");
    }

    private function getEquipmentsFromModifierHolder(ModifierHolderInterface $modifierHolder): array
    {
        // Covers place and player cases
        if ($modifierHolder instanceof EquipmentHolderInterface) {
            return $modifierHolder->getEquipments()->toArray();
        }
        if ($modifierHolder instanceof GameEquipment) {
            return [$modifierHolder];
        }
        if ($modifierHolder instanceof Daedalus) {
            return $this->gameEquipmentRepository->findByDaedalus($modifierHolder);
        }

        $className = $modifierHolder::class;

        throw new \Exception("This eventConfig ({$className}) class is not supported");
    }
}
