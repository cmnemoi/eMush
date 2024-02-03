<?php

namespace Mush\Modifier\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Status\Entity\ChargeStatus;

class ModifierCreationService implements ModifierCreationServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private EventCreationServiceInterface $eventCreationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        EventCreationServiceInterface $eventCreationService,
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->eventCreationService = $eventCreationService;
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
        array $tags,
        \DateTime $time,
        ChargeStatus $chargeStatus = null
    ): void {
        if ($modifierConfig instanceof DirectModifierConfig) {
            $this->createDirectModifier($modifierConfig, $holder, $tags, $time, false);

            // if the direct modifier is reverted on remove we create a gameModifier to keep a trace of its presence
            if ($modifierConfig->getRevertOnRemove()) {
                $this->createGameEventModifier($modifierConfig, $holder, $chargeStatus);
            }
        } else {
            $this->createGameEventModifier($modifierConfig, $holder, $chargeStatus);
        }
    }

    private function createGameEventModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
        ChargeStatus $chargeStatus = null
    ): void {
        $modifier = new GameModifier($holder, $modifierConfig);

        if ($chargeStatus) {
            $modifier->setCharge($chargeStatus);
        }

        $this->persist($modifier);
    }

    public function deleteModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
        array $tags,
        \DateTime $time,
    ): void {
        if (!$modifierConfig instanceof DirectModifierConfig) {
            $this->deleteGameEventModifier($modifierConfig, $holder);
        } elseif ($modifierConfig->getRevertOnRemove()) {
            $this->createDirectModifier($modifierConfig, $holder, $tags, $time, true);
            $this->deleteGameEventModifier($modifierConfig, $holder);
        }
    }

    private function deleteGameEventModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder,
    ): void {
        $modifier = $holder->getModifiers()->getModifierFromConfig($modifierConfig);

        if ($modifier) {
            $this->delete($modifier);
        }
    }

    public function createDirectModifier(
        DirectModifierConfig $modifierConfig,
        ModifierHolderInterface $modifierRange,
        array $tags,
        \DateTime $time,
        bool $reverse
    ): void {
        $triggeredEventConfig = $modifierConfig->getTriggeredEvent();
        $events = $this->eventCreationService->createEvents($triggeredEventConfig, $modifierRange, 0, $tags, $time, $reverse);

        /** @var AbstractGameEvent $event */
        foreach ($events as $event) {
            $this->eventService->callEvent($event, $event->getEventName());
        }
    }
}
