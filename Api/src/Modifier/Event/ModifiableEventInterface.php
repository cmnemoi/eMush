<?php

namespace Mush\Modifier\Event;

use Mush\Modifier\Entity\Collection\ModifierCollection;

interface ModifiableEventInterface
{
    public function getModifiers(): ModifierCollection;
}