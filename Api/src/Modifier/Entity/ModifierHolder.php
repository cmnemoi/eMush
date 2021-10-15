<?php

namespace Mush\Modifier\Entity;

use Doctrine\Common\Collections\Collection;

interface ModifierHolder
{
    public function getModifiers(): Collection;

    public function addModifier(Modifier $modifier): self;

    public function getClassName(): string;
}
