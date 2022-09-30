<?php

namespace Mush\Modifier\Service;

use Mush\Action\Entity\Action;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

interface ModifierServiceInterface
{
    public function persist(Modifier $modifier): Modifier;

    public function delete(Modifier $modifier): void;

    public function getHolderFromConfig(ModifierConfig $config, ModifierHolder $holder, ModifierHolder $target = null) : ModifierHolder;

    public function createModifier(ModifierConfig $config, ModifierHolder $holder) : Modifier;

    public function deleteModifier(Modifier $modifier): void;

    public function getActionModifiedValue(Action $action, Player $player, string $target, ?LogParameterInterface $parameter, ?int $attemptNumber = null): int;

    public function applyActionModifiers(Action $action, Player $player, ?LogParameterInterface $parameter): void;

    public function getEventModifiedValue(
        ModifierHolder $holder,
        array $scopes,
        string $target,
        int $initValue,
        string $reason,
        \DateTime $time,
        bool $applyModifier = true
    ): int;

    public function isSuccessfulWithModifiers(
        int $successRate,
        array $scopes,
        string $reason,
        \DateTime $time,
        ModifierHolder $holder
    ): bool;

    public function playerEnterRoom(Player $player): void;

    public function playerLeaveRoom(Player $player): void;
}
