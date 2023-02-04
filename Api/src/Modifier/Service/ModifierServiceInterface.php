<?php

namespace Mush\Modifier\Service;

use Mush\Action\Entity\Action;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;

interface ModifierServiceInterface
{
    public function persist(GameModifier $modifier): GameModifier;

    public function delete(GameModifier $modifier): void;

    public function createModifier(
        ModifierConfig $modifierConfig,
        ModifierHolder $holder,
        ?ChargeStatus $chargeStatus = null
    ): void;

    public function deleteModifier(
        ModifierConfig $modifierConfig,
        ModifierHolder $holder
    ): void;

    public function getActionModifiedValue(Action $action, Player $player, string $target, ?LogParameterInterface $parameter, ?int $attemptNumber = null): int;

    public function applyActionModifiers(Action $action, Player $player, ?LogParameterInterface $parameter): void;

    public function getEventModifiedValue(
        ModifierHolder $holder,
        array $scopes,
        string $target,
        int $initValue,
        array $reasons,
        \DateTime $time,
        bool $applyModifier = true
    ): int;

    public function isSuccessfulWithModifiers(
        int $successRate,
        array $scopes,
        array $reasons,
        \DateTime $time,
        ModifierHolder $holder
    ): bool;
}
