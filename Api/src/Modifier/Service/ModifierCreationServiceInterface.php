<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierProviderInterface;

interface ModifierCreationServiceInterface
{
    public function persist(GameModifier $modifier): GameModifier;

    public function delete(GameModifier $modifier): void;

    public function createModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
        ModifierProviderInterface $modifierProvider,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ): void;

    public function deleteModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
        ModifierProviderInterface $modifierProvider,
        array $tags = [],
        \DateTime $time = new \DateTime(),
        ?bool $revertOnRemove = null
    ): void;

    public function createDirectModifier(
        DirectModifierConfig $modifierConfig,
        ModifierHolderInterface $modifierRange,
        ModifierProviderInterface $modifierProvider,
        array $tags,
        \DateTime $time,
        bool $reverse
    ): void;
}
