<?php

namespace Mush\Modifier\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Equipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierCondition;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

class ModifierConditionService implements ModifierConditionServiceInterface
{
    private RandomServiceInterface $randomService;

    public function __construct(
        RandomServiceInterface $randomService,
    ) {
        $this->randomService = $randomService;
    }

    public function getActiveModifiers(ModifierCollection $modifiers, string $reason, ModifierHolder $holder): ModifierCollection
    {
        $validatedModifiers = new ModifierCollection();

        foreach ($modifiers as $modifier) {
            $chargeStatus = $modifier->getCharge();
            if (
                $chargeStatus === null ||
                $chargeStatus->getCharge() > 0
            ) {
                if ($this->checkModifier($modifier, $reason, $holder)) {
                    $validatedModifiers->add($modifier);
                }
            }
        }

        return $validatedModifiers;
    }

    private function checkModifier(Modifier $modifier, string $reason, ModifierHolder $holder): bool
    {
        $modifierConfig = $modifier->getModifierConfig();

        foreach ($modifierConfig->getModifierConditions() as $condition) {
            if (!$this->checkCondition($condition, $reason, $holder)) {
                return false;
            }
        }

        return true;
    }

    private function checkCondition(ModifierCondition $condition, string $reason, ModifierHolder $holder): bool
    {
        switch ($condition->getName()) {
            case ModifierConditionEnum::REASON:
                return $reason === $condition->getCondition();

            case ModifierConditionEnum::NOT_REASON:
                return $reason !== $condition->getCondition();

            case ModifierConditionEnum::RANDOM:
                return $this->randomService->isSuccessful(intval($condition->getValue()));

            case ModifierConditionEnum::PLAYER_IN_ROOM:
                return $this->handlePlayerInRoomCondition($condition, $holder);

            case ModifierConditionEnum::CYCLE:
                return $this->handleCycleCondition($condition, $holder);

            case ModifierConditionEnum::PLAYER_EQUIPMENT:
                return $this->handlePlayerEquipmentCondition($condition, $holder);

            case ModifierConditionEnum::ITEM_IN_ROOM:
                return $this->handleItemInRoomCondition($condition, $holder);

            case ModifierConditionEnum::PLAYER_STATUS:
                return $this->handlePlayerStatusCondition($condition, $holder);

            default:
                throw new \LogicException('this condition is not implemented');
        }
    }

    private function handlePlayerInRoomCondition(ModifierCondition $condition, ModifierHolder $holder): bool
    {
        if ($holder instanceof Place) {
            $room = $holder;
        } elseif ($holder instanceof Equipment || $holder instanceof Player) {
            $room = $holder->getPlace();
        } else {
            throw new \LogicException('daedalus cannot be used as holder for a player_in_room condition');
        }

        switch ($condition->getCondition()) {
            case ModifierConditionEnum::NOT_ALONE:
                return $room->getPlayers()->count() >= 2;
            case ModifierConditionEnum::ALONE:
                return $room->getPlayers()->count() === 1;
            case ModifierConditionEnum::FOUR_PEOPLE:
                return $room->getPlayers()->count() >= 4;

            default:
                throw new \LogicException('This condition is invalid for player_in_room');
        }
    }

    private function handleCycleCondition(ModifierCondition $condition, ModifierHolder $holder): bool
    {
        if ($holder instanceof Place || $holder instanceof Player) {
            $daedalus = $holder->getDaedalus();
        } elseif ($holder instanceof Equipment) {
            $daedalus = $holder->getPlace()->getDaedalus();
        } elseif ($holder instanceof Daedalus) {
            $daedalus = $holder;
        } else {
            throw new \LogicException('This modifierHolder type is not handled');
        }

        switch ($condition->getCondition()) {
            case ModifierConditionEnum::EVEN:
                return $daedalus->getCycle() / 2 === intval($daedalus->getCycle() / 2);

            default:
                throw new \LogicException('This condition is invalid for cycle');
        }
    }

    private function handlePlayerEquipmentCondition(ModifierCondition $condition, ModifierHolder $holder): bool
    {
        if (!$holder instanceof Player) {
            throw new \LogicException('PLAYER_EQUIPMENT condition can only be applied on a player');
        }

        /** @var Player $player */
        $player = $holder;

        $expectedItem = $condition->getCondition();

        if ($expectedItem === null) {
            throw new \LogicException('provide an item for player_equipment condition');
        }

        return $player->hasEquipmentByName($expectedItem);
    }

    private function handleItemInRoomCondition(ModifierCondition $condition, ModifierHolder $holder): bool
    {
        if ($holder instanceof Place) {
            $room = $holder;
        } elseif ($holder instanceof Player) {
            $room = $holder->getPlace();
        } else {
            throw new \LogicException('invalid ModifierHolder for item_in_room condition');
        }

        return $room->getEquipments()->filter(function (Equipment $equipment) use ($condition) {
            return $equipment->getName() === $condition->getCondition();
        })->count() > 0;
    }

    private function handlePlayerStatusCondition(ModifierCondition $condition, ModifierHolder $holder): bool
    {
        if (!$holder instanceof Player) {
            throw new \LogicException('PLAYER_STATUS condition can only be applied on a player');
        }
        /** @var Player $player */
        $player = $holder;
        $expectedStatus = $condition->getCondition();
        if ($expectedStatus === null) {
            throw new \LogicException('provide a status for player_status condition');
        }

        return $player->hasStatus($expectedStatus);
    }
}
