<?php

declare(strict_types=1);

namespace Mush\Player\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\ExplorationEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\UpdatePlayerNotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ExplorationEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PlayerServiceInterface $playerService,
        private TranslationServiceInterface $translationService,
        private UpdatePlayerNotificationService $updatePlayerNotification,
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            ExplorationEvent::EXPLORATION_STARTED => 'onExplorationStarted',
            ExplorationEvent::EXPLORATION_FINISHED => 'onExplorationFinished',
        ];
    }

    public function onExplorationStarted(ExplorationEvent $event): void
    {
        $exploration = $event->getExploration();
        $planetPlace = $exploration->getDaedalus()->getPlanetPlace();
        $explorationLink = $this->translatedExplorationLink($event->getExploration()->getClosedExploration());

        $explorators = $exploration->getExplorators();
        foreach ($explorators as $explorator) {
            $this->playerService->changePlace($explorator, $planetPlace);
            if (!$exploration->getActiveExplorators()->exists(static fn ($_, Player $player) => $player === $explorator)) {
                $this->updatePlayerNotification->execute(
                    player: $explorator,
                    message: PlayerNotificationEnum::EXPLORATION_STARTED_NO_SPACESUIT,
                    parameters: [
                        'exploration_link' => $explorationLink,
                    ]
                );
            }
        }
    }

    public function onExplorationFinished(ExplorationEvent $event): void
    {
        $exploration = $event->getExploration();
        $returnPlace = $exploration->getDaedalus()->getPlaceByName($exploration->getStartPlaceName());
        if (!$returnPlace) {
            throw new \RuntimeException("There is no place with name {$exploration->getStartPlaceName()} in this Daedalus");
        }

        foreach ($exploration->getNotLostAliveExplorators() as $explorator) {
            $this->playerService->changePlace($explorator, $returnPlace);
        }

        $this->sendExplorationFinishedNotification($event);
    }

    private function sendExplorationFinishedNotification(ExplorationEvent $event): void
    {
        $notification = match (true) {
            $event->hasTag(ActionEnum::RUN_HOME->toString()) => PlayerNotificationEnum::EXPLORATION_CLOSED_BY_U_TURN,
            $event->hasAllTags([PlanetSectorEvent::BACK, PlanetSectorEnum::MANKAROG]) => PlayerNotificationEnum::EXPLORATION_CLOSED_RETURN_EVENT_MANKAROG,
            $event->hasAllTags([PlanetSectorEvent::BACK, PlanetSectorEnum::SEISMIC_ACTIVITY]) => PlayerNotificationEnum::EXPLORATION_CLOSED_RETURN_EVENT_SEISMIC_ACTIVITY,
            $event->hasAllTags([PlanetSectorEvent::BACK, PlanetSectorEnum::VOLCANIC_ACTIVITY]) => PlayerNotificationEnum::EXPLORATION_CLOSED_RETURN_EVENT_VOLCANIC_ACTIVITY,
            $event->hasAnyTag([DaedalusEvent::FINISH_DAEDALUS, ExplorationEvent::ALL_EXPLORATORS_ARE_DEAD]) => PlayerNotificationEnum::EXPLORATION_CLOSED_EVERYONE_DEAD,
            $event->hasTag(ExplorationEvent::ALL_EXPLORATORS_STUCKED) => PlayerNotificationEnum::EXPLORATION_CLOSED_NO_SPACESUIT,
            default => PlayerNotificationEnum::EXPLORATION_CLOSED,
        };

        $author = $event->getAuthor();
        $explorationLink = $this->translatedExplorationLink($event->getExploration()->getClosedExploration());
        $parameters = ['exploration_link' => $explorationLink];
        if ($author instanceof Player) {
            $parameters[$author->getLogKey()] = $author->getLogName();
        }

        foreach ($event->getExploration()->getNotLostExplorators() as $explorator) {
            $this->updatePlayerNotification->execute(
                player: $explorator,
                message: $notification,
                parameters: $parameters
            );
        }
    }

    private function translatedExplorationLink(ClosedExploration $closedExploration): string
    {
        $explorationUrl = \sprintf('/expPerma/%d', $closedExploration->getId());
        $explorationArchive = $this->translationService->translate(
            key: 'exploration_archive',
            parameters: [],
            domain: 'misc',
            language: $closedExploration->getDaedalusInfo()->getLanguage()
        );

        return \sprintf("<a href='%s'>%s</a>", $explorationUrl, $explorationArchive);
    }
}
