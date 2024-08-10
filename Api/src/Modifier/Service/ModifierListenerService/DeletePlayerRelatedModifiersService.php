<?php

declare(strict_types=1);

namespace Mush\Modifier\Service\ModifierListenerService;

use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Player\Entity\Player;

final class DeletePlayerRelatedModifiersService
{
    public function __construct(private ModifierCreationServiceInterface $modifierCreationService) {}

    public function execute(Player $player, array $tags = [], \DateTime $time = new \DateTime()): void
    {
        /** @var AbstractModifierConfig $modifierConfig */
        foreach ($player->getAllModifierConfigs() as $modifierConfig) {
            $modifierHolder = match ($modifierConfig->getModifierRange()) {
                ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER => $player,
                ModifierHolderClassEnum::DAEDALUS => $player->getDaedalus(),
                ModifierHolderClassEnum::PLACE => $player->getPlace(),
                default => throw new \LogicException("Modifier holded by {$modifierConfig->getModifierRange()} is not related to player : cannot delete it"),
            };

            $this->modifierCreationService->deleteModifier(
                modifierConfig: $modifierConfig,
                holder: $modifierHolder,
                modifierProvider: $player,
                tags: $tags,
                time: $time,
            );
        }
    }
}
