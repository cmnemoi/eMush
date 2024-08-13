<?php

declare(strict_types=1);

namespace Mush\Skill\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Service\DeletePlayerSkillService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DeletePlayerSkillService $deletePlayerSkill,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::CONVERSION_PLAYER => 'onConversionPlayer',
        ];
    }

    public function onConversionPlayer(PlayerEvent $event): void
    {
        if ($event->hasTag(ActionEnum::EXCHANGE_BODY->value)) {
            $this->deletePlayerHumanSkills($event->getPlayer());
        }
    }

    private function deletePlayerHumanSkills(Player $player): void
    {
        $player
            ->getSkills()
            ->filter(static fn (Skill $skill) => $skill->isHumanSkill())
            ->map(fn (Skill $skill) => $this->deletePlayerSkill->execute($skill->getName(), $player));
    }
}
