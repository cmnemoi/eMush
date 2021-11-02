<?php

namespace Mush\Modifier\Service;

use Mush\Equipment\Entity\GameEquipment;
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
            if ($this->checkModifier($modifier, $reason, $holder)) {
                $validatedModifiers->add($modifier);
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
                if (!is_numeric($percentage = $condition->getCondition())) {
                    throw new \LogicException('provide a numeric string condition for random modifier condition');
                }

                return $this->randomService->isSuccessful(intval($percentage));

            case ModifierConditionEnum::PLAYER_IN_ROOM:
                if ($holder instanceof Place) {
                    $room = $holder;
                } elseif ($holder instanceof GameEquipment || $holder instanceof Player) {
                    $room = $holder->getPlace();
                } else {
                    throw new \LogicException('daedalus cannot be used as holder for a player_in_room condition');
                }

                if (!is_numeric($playerNumber = $condition->getCondition())) {
                    throw new \LogicException('provide a numeric string condition for random modifier condition');
                }

                return $room->getPlayers()->count() >= intval($playerNumber);

            default:
                throw new \LogicException('this condition is not implemented');
        }
    }
}
