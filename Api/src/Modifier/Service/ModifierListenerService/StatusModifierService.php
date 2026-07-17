<?php

declare(strict_types=1);

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Status\Entity\Status;

final class StatusModifierService
{
    public function __construct(private ModifierCreationServiceInterface $modifierCreationService) {}

    public function createStatusModifiers(Status $status): void
    {
        $provider = $this->getModifierProvider($status);
        $now = new \DateTime();

        foreach ($status->getAllModifierConfigs() as $modifierConfig) {
            $modifierHolder = match ($modifierConfig->getModifierRange()) {
                ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER => $status->getPlayerOwnerOrThrow(),
                ModifierHolderClassEnum::DAEDALUS => $status->getDaedalusOrThrow(),
                ModifierHolderClassEnum::PLACE => $status->getPlaceOrThrow(),
                ModifierHolderClassEnum::EQUIPMENT => $status->getEquipmentOwnerOrThrow(),
                default => throw new \LogicException("Modifier holded by {$modifierConfig->getModifierRange()} is not related to Status : cannot create it"),
            };

            $this->modifierCreationService->createModifier(
                modifierConfig: $modifierConfig,
                holder: $modifierHolder,
                modifierProvider: $provider,
                tags: [],
                time: $now
            );
        }
    }

    public function deleteStatusModifiers(Status $status): void
    {
        $provider = $this->getModifierProvider($status);
        $now = new \DateTime();

        foreach ($status->getAllModifierConfigs() as $modifierConfig) {
            $modifierHolder = match ($modifierConfig->getModifierRange()) {
                ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER => $status->getPlayerOwnerOrThrow(),
                ModifierHolderClassEnum::DAEDALUS => $status->getDaedalusOrThrow(),
                ModifierHolderClassEnum::PLACE => $status->getPlaceOrThrow(),
                ModifierHolderClassEnum::EQUIPMENT => $status->getEquipmentOwnerOrThrow(),
                default => throw new \LogicException("Modifier holded by {$modifierConfig->getModifierRange()} is not related to Status : cannot delete it"),
            };

            $this->modifierCreationService->deleteModifier(
                modifierConfig: $modifierConfig,
                holder: $modifierHolder,
                modifierProvider: $provider,
                tags: [],
                time: $now
            );
        }
    }

    private function getModifierProvider(Status $status): ModifierProviderInterface
    {
        $statusHolder = $status->getOwner();
        if (
            $statusHolder instanceof ModifierProviderInterface
        ) {
            return $statusHolder;
        }

        return $status;
    }
}
