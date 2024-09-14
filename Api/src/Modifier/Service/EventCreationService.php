<?php

namespace Mush\Modifier\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Collection\ModifierActivationRequirementCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Modifier\Enum\EventTargetNameEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;

final class EventCreationService implements EventCreationServiceInterface
{
    private GameEquipmentRepositoryInterface $gameEquipmentRepository;
    private RandomServiceInterface $randomService;

    private ModifierRequirementServiceInterface $modifierRequirementService;

    public function __construct(
        GameEquipmentRepositoryInterface $gameEquipmentRepository,
        RandomServiceInterface $randomService,
        ModifierRequirementServiceInterface $modifierRequirementService
    ) {
        $this->gameEquipmentRepository = $gameEquipmentRepository;
        $this->randomService = $randomService;
        $this->modifierRequirementService = $modifierRequirementService;
    }

    public function getEventTargetsFromModifierHolder(
        VariableEventConfig $eventConfig,
        ModifierActivationRequirementCollection $eventTargetRequirements,
        array $targetFilters,
        ModifierHolderInterface $range,
        ModifierProviderInterface $author
    ): array {
        $eventTarget = $eventConfig->getVariableHolderClass();

        switch ($eventTarget) {
            case EventTargetNameEnum::DAEDALUS:
                $daedalus = $range->getDaedalus();

                $eventTargets = [$daedalus];

                break;

            case EventTargetNameEnum::PLAYER:
                $eventTargets = $this->getPlayersFromModifierHolder($range)->toArray();

                break;

            case EventTargetNameEnum::EQUIPMENT:
                $eventTargets = $this->getEquipmentsFromModifierHolder($range);

                break;

            default:
                throw new \Exception("This variableHolderClass {$eventTarget} is not supported");
        }

        if (\in_array(EventTargetNameEnum::EXCLUDE_PROVIDER, $targetFilters, true)) {
            $eventTargets = $this->excludeAuthorFromTargets($eventTargets, $author);
        }

        if (\in_array(EventTargetNameEnum::SINGLE_RANDOM, $targetFilters, true)) {
            $eventTargets = $this->randomService->getRandomElements($eventTargets, 1);
        }

        return $this->checkRequirements($eventTargets, $eventTargetRequirements);
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

    private function excludeAuthorFromTargets(array $targets, ModifierProviderInterface $author): array
    {
        return array_filter($targets, static function ($target) use ($author) {
            return !($target === $author);
        });
    }

    private function checkRequirements(array $targets, ModifierActivationRequirementCollection $targetRequirements): array
    {
        return array_filter($targets, function ($target) use ($targetRequirements) {
            return $this->modifierRequirementService->checkRequirements($targetRequirements, $target);
        });
    }
}
