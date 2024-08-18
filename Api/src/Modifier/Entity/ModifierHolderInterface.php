<?php

namespace Mush\Modifier\Entity;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Modifier\Entity\Collection\ModifierCollection;

interface ModifierHolderInterface
{
    public function getModifiers(): ModifierCollection;

    public function getAllModifiers(): ModifierCollection;

    public function addModifier(GameModifier $modifier): static;

    public function removeModifier(GameModifier $modifier): static;

    public function getClassName(): string;

    public function getName(): string;

    public function getDaedalus(): Daedalus;
}
