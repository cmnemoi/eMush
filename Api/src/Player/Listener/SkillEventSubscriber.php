<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\RaisePlayerVariableMaximumServiceInterface;
use Mush\Skill\Event\SkillCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SkillEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private RaisePlayerVariableMaximumServiceInterface $raisePlayerVariableMaximum) {}

    public static function getSubscribedEvents(): array
    {
        return [
            SkillCreatedEvent::class => 'onSkillCreated',
        ];
    }

    public function onSkillCreated(SkillCreatedEvent $event): void
    {
        $this->applyLethargyBonus($event);
    }

    private function applyLethargyBonus(SkillCreatedEvent $event): void
    {
        if ($event->isNotAboutLethargy()) {
            return;
        }

        $player = $event->skillPlayer();
        $this->raisePlayerVariableMaximum->execute(
            player: $player,
            variableName: PlayerVariableEnum::ACTION_POINT,
            delta: $player->getCharacterConfig()->getMaxActionPoint()
        );
    }
}
