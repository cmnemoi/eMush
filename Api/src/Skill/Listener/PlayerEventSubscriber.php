<?php

declare(strict_types=1);

namespace Mush\Skill\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Handler\ColdBloodedHandler;
use Mush\Skill\Service\DeletePlayerSkillService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ColdBloodedHandler $coldBloodedHandler,
        private DeletePlayerSkillService $deletePlayerSkill,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::CONVERSION_PLAYER => 'onConversionPlayer',
            PlayerEvent::DEATH_PLAYER => ['onDeathPlayer', EventPriorityEnum::LOW],
        ];
    }

    public function onConversionPlayer(PlayerEvent $event): void
    {
        if ($event->hasTag(ActionEnum::EXCHANGE_BODY->value)) {
            $this->deletePlayerHumanSkills($event->getPlayer());
        }
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $daedalus = $event->getDaedalus();
        $coldBloodedPlayers = $daedalus
            ->getAlivePlayers()
            ->getHumanPlayer()
            ->getPlayersWithSkill(SkillEnum::COLD_BLOODED);

        $coldBloodedPlayers->map(fn (Player $player) => $this->coldBloodedHandler->execute($player));
    }

    private function deletePlayerHumanSkills(Player $player): void
    {
        $player
            ->getHumanSkills()
            ->map(fn (Skill $skill) => $this->deletePlayerSkill->execute($skill->getName(), $player));
    }
}
