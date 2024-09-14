<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ApplyEffectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private EventServiceInterface $eventService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ApplyEffectEvent::HEAL => 'onHeal',
        ];
    }

    public function onHeal(ApplyEffectEvent $event): void
    {
        $author = $event->getAuthor();
        if ($author->hasModifierByModifierName(ModifierNameEnum::MYCOLOGIST_MODIFIER) && $author->isHuman()) {
            $this->removeOneSporeFromTarget($event);
        }
    }

    private function removeOneSporeFromTarget(ApplyEffectEvent $event): void
    {
        $playerVariableEvent = new PlayerVariableEvent(
            player: $event->getPlayerActionTarget(),
            variableName: PlayerVariableEnum::SPORE,
            quantity: $this->mycologistBonus($event),
            tags: $event->getTags(),
            time: $event->getTime(),
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function mycologistBonus(ApplyEffectEvent $event): int
    {
        $author = $event->getAuthor();

        return $author
            ->getModifiers()
            ->getModifierByModifierNameOrThrow(ModifierNameEnum::MYCOLOGIST_MODIFIER)
            ->getTriggerModifierConfigOrThrow()
            ->getTriggeredVariableEventConfigOrThrow()
            ->getQuantity();
    }
}
