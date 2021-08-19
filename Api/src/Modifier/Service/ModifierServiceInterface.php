<?php

namespace Mush\Modifier\Service;

use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameter;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;

interface ModifierServiceInterface
{
    public function persist(Modifier $modifier): Modifier;

    public function delete(Modifier $modifier): void;

    public function createModifier(
        ModifierConfig $modifierConfig,
        Daedalus $daedalus,
        ?Place $place,
        ?Player $player,
        ?GameEquipment $gameEquipment,
        ?ChargeStatus $chargeStatus
    ): void;

    public function deleteModifier(
        ModifierConfig $modifierConfig,
        Daedalus $daedalus,
        ?Place $place,
        ?Player $player,
        ?GameEquipment $gameEquipment
    ): void;

    public function getActionModifiedValue(Action $action, Player $player, string $target, ?ActionParameter $parameter, ?int $attemptNumber = null): int;

    public function consumeActionCharges(Action $action, Player $player, ?ActionParameter $parameter): void;

    public function getEventModifiedValue(Player $player, array $scopes, string $target, int $initValue): int;
}
