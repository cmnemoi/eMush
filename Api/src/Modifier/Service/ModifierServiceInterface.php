<?php

namespace Mush\Modifier\Service;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameter;
use Mush\Modifier\Entity\Modifier;
use Mush\Player\Entity\Player;

interface ModifierServiceInterface
{
    public function persist(Modifier $modifier): Modifier;

    public function delete(Modifier $modifier): void;

    public function getActionModifiedValue(Action $action, Player $player, string $target, ?ActionParameter $parameter, ?int $attemptNumber): int;

    public function consumeActionCharges(AbstractAction $action): void;

    public function getEventModifiedValue(Player $player, array $scopes, string $target, int $initValue): int;

    public function consumeEventCharges(Player $player, array $scopes, int $initValue): void;
}
