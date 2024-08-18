<?php

declare(strict_types=1);

namespace Mush\Skill\Listener;

use Mush\Player\Entity\Player;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Service\DeletePlayerSkillService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class StatusEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DeletePlayerSkillService $deletePlayerSkill,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $statusName = $event->getStatusName();
        if ($statusName === PlayerStatusEnum::MUSH) {
            $this->removePlayerSkills($event);
        }
    }

    private function removePlayerSkills(StatusEvent $event): void
    {
        /** @var Player $player */
        $player = $event->getStatusHolder();

        $player->getSkills()->map(fn (Skill $skill) => $this->deletePlayerSkill->execute($skill->getName(), $player));
    }
}
