<?php

declare(strict_types=1);

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierProviderInterface;

final class FakeModifierCreationService implements ModifierCreationServiceInterface
{
    /** @var GameModifier[] */
    private array $repository = [];

    public function persist(GameModifier $modifier): GameModifier
    {
        $this->repository[$modifier->getId()] = $modifier;

        return $modifier;
    }

    public function delete(GameModifier $modifier): void
    {
        $holder = $modifier->getModifierHolder();
        $holder->removeModifier($modifier);

        unset($this->repository[$modifier->getId()]);
    }

    public function createModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
        ModifierProviderInterface $modifierProvider,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void {}

    public function deleteModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
        ModifierProviderInterface $modifierProvider,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void {
        // delete all modifiers with the same config
        foreach ($this->repository as $modifier) {
            if ($modifier->getModifierConfig()->getId() === $modifierConfig->getId()) {
                $this->delete($modifier);
            }
        }
    }

    public function createDirectModifier(
        DirectModifierConfig $modifierConfig,
        ModifierHolderInterface $modifierRange,
        ModifierProviderInterface $modifierProvider,
        array $tags,
        \DateTime $time,
        bool $reverse
    ): void {}

    public function clearRepository(): void
    {
        $this->repository = [];
    }

    public function findOneById(int $id): GameModifier
    {
        return $this->repository[$id] ?? GameModifier::createNullEventModifier();
    }
}
