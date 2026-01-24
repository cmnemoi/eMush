<?php

declare(strict_types=1);

namespace Mush\Modifier\Repository;

use Mush\Modifier\Entity\Config\AbstractModifierConfig;

interface ModifierConfigRepositoryInterface
{
    public function findByName(string $name): ?AbstractModifierConfig;

    public function save(AbstractModifierConfig $modifierConfig): void;
}
