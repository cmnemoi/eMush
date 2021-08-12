<?php

namespace Mush\Modifier\Service;

use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameter;
use Mush\Modifier\Entity\Modifier;
use Mush\Player\Entity\Player;

interface ModifierServiceInterface
{
    public function persist(Modifier $modifier): Modifier;

    public function delete(Modifier $modifier): void;

    public function getActionModifiedValue(Action $action, Player $player, string $target, ?ActionParameter $parameter, ?int $attemptNumber = null): int;

    public function consumeActionCharges(Action $action, Player $player, ?ActionParameter $parameter): void;

    public function getEventModifiedValue(Player $player, array $scopes, string $target, int $initValue): int;
}
