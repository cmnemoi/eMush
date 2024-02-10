<?php

declare(strict_types=1);

namespace Mush\Exploration\Listener;

use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Service\ExplorationServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PlanetSectorEventSubscriber implements EventSubscriberInterface
{
    private ExplorationServiceInterface $explorationService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        ExplorationServiceInterface $explorationService,
        TranslationServiceInterface $translationService
    ) {
        $this->explorationService = $explorationService;
        $this->translationService = $translationService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlanetSectorEvent::ACCIDENT => 'onAccident',
            PlanetSectorEvent::DISASTER => 'onDisaster',
            PlanetSectorEvent::NOTHING_TO_REPORT => 'onNothingToReport',
            PlanetSectorEvent::TIRED => 'onTired',
        ];
    }

    public function onAccident(PlanetSectorEvent $event): void
    {
        $logParameters = $this->explorationService->removeHealthToARandomExplorator($event);

        $this->explorationService->createExplorationLog($event, $logParameters);
    }

    public function onDisaster(PlanetSectorEvent $event): void
    {
        $logParameters = $this->explorationService->removeHealthToAllExplorators($event);

        $this->explorationService->createExplorationLog($event, $logParameters);
    }

    public function onNothingToReport(PlanetSectorEvent $event): void
    {
        $logParameters = [];
        $logParameters['always_successful_thanks_to_skill'] = $this->getAlwaysSuccessfulThanksToSkillLogParameter($event);

        $this->explorationService->createExplorationLog($event, $logParameters);
    }

    public function onTired(PlanetSectorEvent $event): void
    {
        $logParameters = $this->explorationService->removeHealthToAllExplorators($event);

        $this->explorationService->createExplorationLog($event, $logParameters);
    }

    private function getAlwaysSuccessfulThanksToSkillLogParameter(PlanetSectorEvent $event): ?string
    {
        $language = $event->getExploration()->getDaedalus()->getLanguage();
        if ($event->hasTag('always_successful_thanks_to_pilot')) {
            $skill = $this->translationService->translate(
                key: 'pilot.name',
                parameters: [],
                domain: 'skill',
                language: $language,
            );

            $alwaysSuccessfulThanksToSkill = $this->translationService->translate(
                key: 'always_successful_thanks_to_skill',
                parameters: ['skill' => $skill],
                domain: 'planet_sector_event',
                language: $language,
            );

            return '//' . $alwaysSuccessfulThanksToSkill;
        }

        return null;
    }
}
