<?php

namespace Mush\Player\Service;

use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Service\ModifierServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;

class PlayerVariableService implements PlayerVariableServiceInterface
{
    private ModifierServiceInterface $modifierService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        ModifierServiceInterface $modifierService,
        PlayerServiceInterface $playerService,
    ) {
        $this->modifierService = $modifierService;
        $this->playerService = $playerService;
    }

    public function getMaxPlayerVariable(Player $player, string $variableName): ?int
    {
        $variable = $player->getVariableByName($variableName);

        $maxValue = $variable->getMaxValue();

        if ($maxValue === null) {
            return null;
        }

        return $this->modifierService->getEventModifiedValue(
            $player,
            [ModifierScopeEnum::MAX_POINT],
            $variableName,
            $maxValue,
            [ModifierScopeEnum::MAX_POINT],
            new \DateTime()
        );
    }

    public function setPlayerVariableToMax(Player $player, string $variableName, \DateTime $date = null): Player
    {
        $maxAmount = $this->getMaxPlayerVariable($player, $variableName);
        $delta = $maxAmount - $player->getVariableValueFromName($variableName);

        $newAmount = $this->getValueInInterval($maxAmount + $delta, 0, $maxAmount);

        $player->setVariableValueFromName($variableName, $newAmount);

        return $this->playerService->persist($player);
    }

    public function handleGameVariableChange(string $variableName, int $delta, Player $player): Player
    {
        if ($variableName === PlayerVariableEnum::SATIETY) {
            $newVariableValuePoint = $this->getSatietyChange($delta, $player);
        } else {
            $newVariableValuePoint = $player->getVariableValueFromName($variableName) + $delta;
            $maxVariableValuePoint = $this->getMaxPlayerVariable($player, $variableName);
            $newVariableValuePoint = $this->getValueInInterval($newVariableValuePoint, 0, $maxVariableValuePoint);
        }

        $player->setVariableValueFromName($variableName, $newVariableValuePoint);

        return $this->playerService->persist($player);
    }

    private function getSatietyChange(int $delta, Player $player): int
    {
        if ($delta >= 0 &&
            $player->getSatiety() < 0
        ) {
            return $delta;
        } else {
            return $player->getSatiety() + $delta;
        }
    }

    private function getValueInInterval(int $value, ?int $min, ?int $max): int
    {
        if ($max !== null && $value > $max) {
            return $max;
        } elseif ($min !== null && $value < $min) {
            return $min;
        }

        return $value;
    }
}
