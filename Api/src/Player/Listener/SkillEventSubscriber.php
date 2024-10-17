<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\ChangePlayerVariableMaximumServiceInterface;
use Mush\Skill\Event\SkillCreatedEvent;
use Mush\Skill\Event\SkillDeletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SkillEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ChangePlayerVariableMaximumServiceInterface $changePlayerVariableMaximum,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            SkillCreatedEvent::class => 'onSkillCreated',
            SkillDeletedEvent::class => 'onSkillDeleted',
        ];
    }

    public function onSkillCreated(SkillCreatedEvent $event): void
    {
        $this->applyLethargyBonus($event);
    }

    public function onSkillDeleted(SkillDeletedEvent $event): void
    {
        $this->revertLethargyBonus($event);
    }

    private function applyLethargyBonus(SkillCreatedEvent $event): void
    {
        if ($event->isNotAboutLethargy()) {
            return;
        }

        $player = $event->skillPlayer();
        $this->changePlayerVariableMaximum->execute(
            player: $player,
            variableName: PlayerVariableEnum::ACTION_POINT,
            delta: $player->getCharacterConfig()->getMaxActionPoint()
        );
    }

    private function revertLethargyBonus(SkillDeletedEvent $event): void
    {
        if ($event->isNotAboutLethargy()) {
            return;
        }

        $player = $event->skillPlayer();
        $this->changePlayerVariableMaximum->execute(
            player: $player,
            variableName: PlayerVariableEnum::ACTION_POINT,
            delta: -$player->getCharacterConfig()->getMaxActionPoint()
        );
    }
}
