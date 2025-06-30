<?php

namespace Mush\Player\Listener;

use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onProjectFinished(ProjectEvent $event): void
    {
        $player = $event->getAuthor();

        if ($player->isNull()) {
            return;
        }

        $player->addPlayerHighlight(PlayerHighlight::fromEventForAuthor($event));

        $this->playerRepository->save($player);
    }
}
