<?php

namespace Mush\Player\Service;

use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;

class PlayerVariableService implements PlayerVariableServiceInterface
{
    private PlayerServiceInterface $playerService;

    public function __construct(
        PlayerServiceInterface $playerService,
    ) {
        $this->playerService = $playerService;
    }

    public function setPlayerVariableToMax(Player $player, string $variableName, \DateTime $date = null): Player
    {
        $maxAmount = $player->getVariableByName($variableName)->getMaxValue();
        $delta = $maxAmount - $player->getVariableValueByName($variableName);

        $newAmount = $this->getValueInInterval($maxAmount + $delta, 0, $maxAmount);

        $player->setVariableValueByName($variableName, $newAmount);

        return $this->playerService->persist($player);
    }

    public function handleGameVariableChange(string $variableName, int $delta, Player $player): Player
    {
        if ($variableName === PlayerVariableEnum::SATIETY) {
            $newVariableValuePoint = $this->getSatietyChange($delta, $player);
        } else {
            $newVariableValuePoint = $player->getVariableValueByName($variableName) + $delta;
            $maxVariableValuePoint = $player->getVariableByName($variableName)->getMaxValue();
            $newVariableValuePoint = $this->getValueInInterval($newVariableValuePoint, 0, $maxVariableValuePoint);
        }

        $player->setVariableValueByName($variableName, $newVariableValuePoint);

        return $this->playerService->persist($player);
    }

    private function getSatietyChange(int $delta, Player $player): int
    {
        if ($delta >= 0
            && $player->getSatiety() < 0
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
