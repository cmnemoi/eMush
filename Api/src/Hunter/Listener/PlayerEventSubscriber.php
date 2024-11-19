<?php

declare(strict_types=1);

namespace Mush\Hunter\Listener;

use Mush\Hunter\Repository\HunterRepositoryInterface;
use Mush\Hunter\Repository\HunterTargetRepositoryInterface;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlayerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private HunterTargetRepositoryInterface $hunterTargetRepository,
        private HunterRepositoryInterface $hunterRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerEvent::DELETE_PLAYER => 'onDeletePlayer',
        ];
    }

    public function onDeletePlayer(PlayerEvent $event): void
    {
        $this->deleteHunterTarget($event);
    }

    private function deleteHunterTarget(PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $hunterTargets = $this->hunterTargetRepository->findAllBy(['player' => $player]);

        foreach ($hunterTargets as $hunterTarget) {
            $owner = $this->hunterRepository->findOneByTargetOrThrow($hunterTarget);
            $owner->resetTarget();
            $this->hunterRepository->save($owner);
        }
    }
}
