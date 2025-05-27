<?php

namespace Mush\Modifier\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierProviderInterface;

class ModifierCreationService implements ModifierCreationServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private EventCreationServiceInterface $eventCreationService;
    private ModifierRequirementServiceInterface $modifierRequirementService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        EventCreationServiceInterface $eventCreationService,
        ModifierRequirementServiceInterface $modifierRequirementService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->eventCreationService = $eventCreationService;
        $this->modifierRequirementService = $modifierRequirementService;
    }

    public function persist(GameModifier $modifier): GameModifier
    {
        $this->entityManager->persist($modifier);
        $this->entityManager->flush();

        return $modifier;
    }

    public function delete(GameModifier $modifier): void
    {
        $this->entityManager->remove($modifier);
        $this->entityManager->flush();
    }

    public function createModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
        ModifierProviderInterface $modifierProvider,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void {
        if ($modifierConfig instanceof DirectModifierConfig) {
            $this->createDirectModifier(
                modifierConfig: $modifierConfig,
                modifierRange: $holder,
                modifierProvider: $modifierProvider,
                tags: $tags,
                time: $time,
                reverse: false
            );

            // if the direct modifier is reverted on remove we create a gameModifier to keep a trace of its presence
            if ($modifierConfig->getRevertOnRemove()) {
                $this->createGameEventModifier($modifierConfig, $holder, $modifierProvider);
            }
        } else {
            $this->createGameEventModifier($modifierConfig, $holder, $modifierProvider);
        }
    }

    public function deleteModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
        ModifierProviderInterface $modifierProvider,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ): void {
        if (!$modifierConfig instanceof DirectModifierConfig) {
            $this->deleteGameEventModifier(
                modifierConfig: $modifierConfig,
                holder: $holder,
                modifierProvider: $modifierProvider
            );
        } elseif ($modifierConfig->getRevertOnRemove()) {
            $this->createDirectModifier($modifierConfig, $holder, $modifierProvider, $tags, $time, true);
            $this->deleteGameEventModifier(
                $modifierConfig,
                $holder,
                $modifierProvider
            );
        }
    }

    public function createDirectModifier(
        DirectModifierConfig $modifierConfig,
        ModifierHolderInterface $modifierRange,
        ModifierProviderInterface $modifierProvider,
        array $tags,
        \DateTime $time,
        bool $reverse
    ): void {
        $triggeredEventConfig = $modifierConfig->getTriggeredEvent();

        if ($reverse) {
            $triggeredEventConfig = $triggeredEventConfig->revertEvent();
        }

        if ($triggeredEventConfig instanceof VariableEventConfig) {
            $this->appliesVariableDirectModifier(
                $triggeredEventConfig,
                $modifierConfig,
                $modifierRange,
                $modifierProvider,
                $tags,
                $time,
            );
        }
    }

    public function appliesVariableDirectModifier(
        VariableEventConfig $eventConfig,
        DirectModifierConfig $modifierConfig,
        ModifierHolderInterface $modifierRange,
        ModifierProviderInterface $modifierProvider,
        array $tags,
        \DateTime $time,
    ): void {
        if (!$this->modifierRequirementService->checkRequirements($modifierConfig->getModifierActivationRequirements(), $modifierRange)) {
            return;
        }
        $eventTargets = $this->eventCreationService->getEventTargetsFromModifierHolder(
            eventConfig: $eventConfig,
            eventTargetRequirements: $modifierConfig->getEventActivationRequirements(),
            targetFilters: $modifierConfig->getTargetFilters(),
            range: $modifierRange,
            author: $modifierProvider
        );

        /** @var ModifierHolderInterface $eventTarget */
        foreach ($eventTargets as $eventTarget) {
            $event = $eventConfig->createEvent(0, $tags, $time, $eventTarget);

            if ($event !== null) {
                $this->eventService->callEvent($event, $event->getEventName());
            }
        }
    }

    private function createGameEventModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
        ModifierProviderInterface $modifierProvider
    ): void {
        $modifier = new GameModifier($holder, $modifierConfig);
        $modifier->setModifierProvider($modifierProvider);

        $this->persist($modifier);
    }

    private function deleteGameEventModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
        ModifierProviderInterface $modifierProvider
    ): void {
        $modifier = $holder->getModifiers()->getModifierFromConfigAndProvider($modifierConfig, $modifierProvider);

        if ($modifier) {
            $this->delete($modifier);
        }
    }
}
