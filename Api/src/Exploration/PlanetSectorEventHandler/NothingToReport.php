<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;

final class NothingToReport extends AbstractPlanetSectorEventHandler
{
    private TranslationServiceInterface $translationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        TranslationServiceInterface $translationService
    ) {
        parent::__construct($entityManager, $eventService, $randomService);
        $this->translationService = $translationService;
    }

    public function getName(): string
    {
        return PlanetSectorEvent::NOTHING_TO_REPORT;
    }

    public function handle(PlanetSectorEvent $event): void
    {
        $logParameters = [];
        $logParameters['always_successful_thanks_to_skill'] = $this->getAlwaysSuccessfulThanksToSkillLogParameter($event);

        $this->createExplorationLog($event, $logParameters);
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

            return '////' . $alwaysSuccessfulThanksToSkill;
        }

        return null;
    }
}
