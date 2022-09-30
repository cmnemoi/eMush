<?php

namespace Mush\Modifier\Entity;

interface ModifierHolder
{
    public function getModifiers(): ModifierCollection;

    public function getModifiersAtReach(): ModifierCollection;

    public function addModifier(Modifier $modifier): self;

    public function getClassName(): string;
}
