<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Modifier\TestDoubles;

use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Repository\ModifierConfigRepositoryInterface;

class InMemoryModifierConfigRepository implements ModifierConfigRepositoryInterface
{
    private array $modifiers = [];

    public function findByName(string $name): ?AbstractModifierConfig
    {
        return current(
            array_filter(
                $this->modifiers,
                static fn (AbstractModifierConfig $modifier) => $modifier->getName() === $name
            )
        ) ?: new EventModifierConfig($name);
    }

    public function save(AbstractModifierConfig $modifierConfig): void
    {
        $this->modifiers[$modifierConfig->getName()] = $modifierConfig;
    }
}
