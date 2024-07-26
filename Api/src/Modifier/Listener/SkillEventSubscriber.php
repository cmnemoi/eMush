<?php

declare(strict_types=1);

namespace Mush\Modifier\Listener;

use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Skill\Event\SkillAddedToPlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SkillEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private ModifierCreationServiceInterface $modifierCreationService) {}

    public static function getSubscribedEvents(): array
    {
        return [
            SkillAddedToPlayerEvent::class => 'onSkillAddedToPlayer',
        ];
    }

    public function onSkillAddedToPlayer(SkillAddedToPlayerEvent $event): void
    {
        $skill = $event->getSkill();

        foreach ($skill->getModifierConfigs() as $modifierConfig) {
            $modifierHolder = match ($modifierConfig->getModifierRange()) {
                ModifierHolderClassEnum::PLAYER => $skill->getPlayer(),
                ModifierHolderClassEnum::DAEDALUS => $event->getDaedalus(),
                default => throw new \InvalidArgumentException("You can't create skill modifier {$modifierConfig->getName()} on a {$modifierConfig->getModifierRange()} holder !"),
            };

            $this->modifierCreationService->createModifier(
                $modifierConfig,
                $modifierHolder,
                $event->getTags(),
                $event->getTime()
            );
        }
    }
}
