<?php

namespace Mush\Modifier\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
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

            case ModifierConditionEnum::RANDOM:
                if (($percentage = $condition->getValue()) === null) {
                    throw new \LogicException('provide a numeric value for random modifier condition');
                }

                return $this->randomService->isSuccessful(intval($percentage));

            case ModifierConditionEnum::PLAYER_IN_ROOM:
                return $this->playerAloneInRoom($condition, $holder);

            case ModifierConditionEnum::CYCLE:
                return $this->handleCycleCondition($condition, $holder);

            case ModifierConditionEnum::PLAYER_EQUIPMENT:
                return $this->handlePlayerEquipmentCondition($condition, $holder);

            default:
                throw new \LogicException('this condition is not implemented');
        }
    }

    private function playerAloneInRoom(ModifierCondition $condition, ModifierHolder $holder): bool
    {
        if ($holder instanceof Place) {
            $room = $holder;
        } elseif ($holder instanceof GameEquipment || $holder instanceof Player) {
            $room = $holder->getPlace();
        } else {
            throw new \LogicException('daedalus cannot be used as holder for a player_in_room condition');
        }

        switch ($condition->getCondition()) {
            case ModifierConditionEnum::NOT_ALONE:
                return $room->getPlayers()->count() >= 2;
            case ModifierConditionEnum::ALONE:
                return $room->getPlayers()->count() === 2;

            default:
                throw new \LogicException('This condition is invalid for player_in_room');
        }
    }

    private function handleCycleCondition(ModifierCondition $condition, ModifierHolder $holder): bool
    {
        if ($holder instanceof Place || $holder instanceof Player) {
            $daedalus = $holder->getDaedalus();
        } elseif ($holder instanceof GameEquipment) {
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

    private function handlePlayerEquipmentCondition(ModifierCondition $condition, ModifierHolder $player): bool
    {
        if (!$player instanceof Player) {
            throw new \LogicException('This modifierHolder type is not handled, should be Player');
        }

        switch ($condition->getCondition()) {
            case ModifierConditionEnum::HOLD_SCHRODINGER:
                return $player->hasItemByName(ItemEnum::SCHRODINGER);

            default:
                throw new \LogicException('This condition is invalid for player_equipment');
        }
    }
}
