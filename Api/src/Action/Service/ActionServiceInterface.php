<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

interface ActionServiceInterface
{
    public function applyCostToPlayer(Player $player, Action $action, ?LogParameterInterface $parameter): Player;

    public function getTotalActionPointCost(
        Player $player,
        Action $action,
        ?LogParameterInterface $parameter,
        bool $consumeCharge = false
    ): int;

    public function getTotalMovementPointCost(Player $player, Action $action, ?LogParameterInterface $parameter): int;

    public function getTotalMoralPointCost(Player $player, Action $action, ?LogParameterInterface $parameter): int;

    public function getSuccessRate(
        Action $action,
        Player $player,
        ?LogParameterInterface $parameter
    ): int;

    public function getCriticalSuccessRate(
        Action $action,
        Player $player,
        ?LogParameterInterface $parameter
    ): int;
}
