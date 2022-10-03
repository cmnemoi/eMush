<?php

namespace Mush\Modifier\Service;

use Mush\Game\Event\AbstractModifierHolderEvent;

interface ModifierListenerServiceInterface {

    public function applyModifiers(AbstractModifierHolderEvent $event) : bool;

}