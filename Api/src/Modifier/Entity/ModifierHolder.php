<?php

namespace Mush\Modifier\Entity;

use Mush\Modifier\Entity\Collection\ModifierCollection;

interface ModifierHolder
{
    public function getModifiers(): ModifierCollection;

    public function getAllModifiers(): ModifierCollection;

    public function addModifier(GameModifier $modifier): self;

    public function getClassName(): string;
}
