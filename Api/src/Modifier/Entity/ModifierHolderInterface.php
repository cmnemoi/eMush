<?php

namespace Mush\Modifier\Entity;

use Mush\Modifier\Entity\Collection\ModifierCollection;

interface ModifierHolderInterface
{
    public function getModifiers(): ModifierCollection;

    public function getAllModifiers(): ModifierCollection;

    public function addModifier(GameModifier $modifier): self;

    public function getClassName(): string;

    public function getName(): string;
}
