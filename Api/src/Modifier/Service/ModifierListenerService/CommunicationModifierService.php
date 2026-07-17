<?php

declare(strict_types=1);

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Communications\Entity\RebelBase;
use Mush\Communications\Entity\XylophEntry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Modifier\Service\ModifierCreationServiceInterface;

final class CommunicationModifierService
{
    public function __construct(private ModifierCreationServiceInterface $modifierCreationService) {}

    public function createRebelBaseModifiers(RebelBase $rebelBase, Daedalus $daedalus): void
    {
        foreach ($rebelBase->getModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $daedalus,
                modifierProvider: $rebelBase,
                tags: [],
                time: new \DateTime(),
            );
        }
    }

    public function deleteRebelBaseModifiers(RebelBase $rebelBase, Daedalus $daedalus): void
    {
        foreach ($rebelBase->getModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->deleteModifier(
                modifierConfig: $modifierConfig,
                holder: $daedalus,
                modifierProvider: $rebelBase,
                tags: [],
                time: new \DateTime(),
            );
        }
    }

    public function createXylophModifiers(XylophEntry $entry, Daedalus $daedalus): void
    {
        foreach ($entry->getModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $daedalus,
                modifierProvider: $entry,
                tags: [],
                time: new \DateTime(),
            );
        }
    }

    public function deleteXylophModifiers(XylophEntry $entry, Daedalus $daedalus): void
    {
        foreach ($entry->getModifierConfigs() as $modifierConfig) {
            $this->modifierCreationService->deleteModifier(
                modifierConfig: $modifierConfig,
                holder: $daedalus,
                modifierProvider: $entry,
                tags: [],
                time: new \DateTime(),
            );
        }
    }
}
