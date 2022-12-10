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

    public function getMaxPlayerVariable(Player $player, string $target): int
    {
        $characterConfig = $player->getPlayerInfo()->getCharacterConfig();

        switch ($target) {
            case PlayerVariableEnum::ACTION_POINT:
                $maxValue = $characterConfig->getMaxActionPoint();
                break;
            case PlayerVariableEnum::MOVEMENT_POINT:
                $maxValue = $characterConfig->getMaxMovementPoint();
                break;
            case PlayerVariableEnum::HEALTH_POINT:
                $maxValue = $characterConfig->getMaxHealthPoint();
                break;
            case PlayerVariableEnum::MORAL_POINT:
                $maxValue = $characterConfig->getMaxMoralPoint();
                break;
            default:
                throw new \Error('getMaxPlayerVariable : invalid target string');
        }

        return $this->modifierService->getEventModifiedValue(
            $player,
            [ModifierScopeEnum::MAX_POINT],
            $target,
            $maxValue,
            ModifierScopeEnum::MAX_POINT,
            new \DateTime()
        );
    }

    public function setPlayerVariableToMax(Player $player, string $target, \DateTime $date = null): Player
    {
        $maxAmount = $this->getMaxPlayerVariable($player, $target);
        $delta = $maxAmount - $player->getVariableFromName($target);

        $newAmount = $this->getValueInInterval($maxAmount + $delta, 0, $maxAmount);

        $player->setVariableFromName($target, $newAmount);

        return $this->playerService->persist($player);
    }

    public function handleActionPointModifier(int $delta, Player $player): Player
    {
        $playerNewActionPoint = $player->getActionPoint() + $delta;
        $playerMaxActionPoint = $this->getMaxPlayerVariable($player, PlayerVariableEnum::ACTION_POINT);
        $playerNewActionPoint = $this->getValueInInterval($playerNewActionPoint, 0, $playerMaxActionPoint);
        $player->setActionPoint($playerNewActionPoint);

        return $this->playerService->persist($player);
    }

    public function handleMovementPointModifier(int $delta, Player $player): Player
    {
        $playerNewMovementPoint = $player->getMovementPoint() + $delta;
        $playerMaxMovementPoint = $this->getMaxPlayerVariable($player, PlayerVariableEnum::MOVEMENT_POINT);
        $playerNewMovementPoint = $this->getValueInInterval($playerNewMovementPoint, 0, $playerMaxMovementPoint);
        $player->setMovementPoint($playerNewMovementPoint);

        return $player;
    }

    public function handleHealthPointModifier(int $delta, Player $player): Player
    {
        $playerNewHealthPoint = $player->getHealthPoint() + $delta;
        $playerMaxHealthPoint = $this->getMaxPlayerVariable($player, PlayerVariableEnum::HEALTH_POINT);
        $playerNewHealthPoint = $this->getValueInInterval($playerNewHealthPoint, 0, $playerMaxHealthPoint);
        $player->setHealthPoint($playerNewHealthPoint);

        return $this->playerService->persist($player);
    }

    public function handleMoralPointModifier(int $delta, Player $player): Player
    {
        if (!$player->isMush()) {
            $playerNewMoralPoint = $player->getMoralPoint() + $delta;
            $playerMaxMoralPoint = $this->getMaxPlayerVariable($player, PlayerVariableEnum::MORAL_POINT);
            $playerNewMoralPoint = $this->getValueInInterval($playerNewMoralPoint, 0, $playerMaxMoralPoint);
            $player->setMoralPoint($playerNewMoralPoint);
        }

        return $this->playerService->persist($player);
    }

    public function handleSatietyModifier(int $delta, Player $player): Player
    {
        if ($delta >= 0 &&
            $player->getSatiety() < 0
        ) {
            $player->setSatiety($delta);
        } else {
            $player->setSatiety($player->getSatiety() + $delta);
        }

        return $this->playerService->persist($player);
    }

    private function getValueInInterval(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
