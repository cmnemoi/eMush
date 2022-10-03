<?php

namespace Mush\Player\Service;

use Error;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\ResourceMaxPointEvent;

class PlayerVariableService implements PlayerVariableServiceInterface
{
    private PlayerServiceInterface $playerService;
    private EventServiceInterface $eventService;

    public function __construct(
        PlayerServiceInterface $playerService,
        EventServiceInterface $eventService
    ) {
        $this->playerService = $playerService;
        $this->eventService = $eventService;
    }

    public function getMaxPlayerVariable(Player $player, string $variable): int
    {
        $gameConfig = $player->getDaedalus()->getGameConfig();

        $baseValue = match ($variable) {
            PlayerVariableEnum::ACTION_POINT => $gameConfig->getMaxActionPoint(),
            PlayerVariableEnum::MOVEMENT_POINT => $gameConfig->getMaxMovementPoint(),
            PlayerVariableEnum::HEALTH_POINT => $gameConfig->getMaxHealthPoint(),
            PlayerVariableEnum::MORAL_POINT => $gameConfig->getMaxMoralPoint(),
            default => throw new Error('getMaxPlayerVariable : invalid target string'),
        };

        return $this->getMaxPlayerVariableWithModifier($player, $variable, $baseValue);
    }

    private function getMaxPlayerVariableWithModifier(Player $player, string $variable, int $baseValue): int
    {
        $event = new ResourceMaxPointEvent(
            $player,
            $variable,
            $baseValue,
            ResourceMaxPointEvent::CHECK_MAX_POINT,
            new \DateTime()
        );

        $this->eventService->callEvent($event, ResourceMaxPointEvent::CHECK_MAX_POINT);

        return $event->getValue();
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
        if ($delta >= 0 && $player->getSatiety() < 0) {
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
