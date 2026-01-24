<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Modifier\TestDoubles;

use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Repository\ModifierRepositoryInterface;

final class InMemoryModifierRepository implements ModifierRepositoryInterface
{
    private array $modifiers = [];

    public function save(GameModifier $gameModifier): void
    {
        $this->modifiers[] = $gameModifier;
    }

    public function delete(GameModifier $gameModifier): void
    {
        $this->modifiers = array_filter(
            $this->modifiers,
            static fn (GameModifier $modifier) => $modifier !== $gameModifier
        );
    }

    public function wrapInTransaction(callable $callable): void
    {
        $callable();
    }

    public function findByName(string $name): ?GameModifier
    {
        return current(
            array_filter(
                $this->modifiers,
                static fn (GameModifier $modifier) => $modifier->getModifierConfig()->getName() === $name
            )
        ) ?: null;
    }

    public function findOneBy(array $criteria): ?GameModifier
    {
        if (\array_key_exists('name', $criteria)) {
            $name = $criteria['name'];

            return $this->modifiers[$name];
        }

        return null;
    }
}
