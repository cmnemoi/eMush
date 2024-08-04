<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Skill\Enum\SkillEnum;

final class NothingToReport extends AbstractPlanetSectorEventHandler
{
    private const NUMBER_OF_VERSIONS = 2;

    public function getName(): string
    {
        return PlanetSectorEvent::NOTHING_TO_REPORT;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $logParameters = $this->getLogParameters($event);
        $logParameters['always_successful_thanks_to_skill'] = $this->getAlwaysSuccessfulThanksToSkillLogParameter($event);
        $logParameters['version'] = $this->randomService->random(1, self::NUMBER_OF_VERSIONS);

        return $this->createExplorationLog($event, $logParameters);
    }

    private function getAlwaysSuccessfulThanksToSkillLogParameter(PlanetSectorEvent $event): ?string
    {
        $language = $event->getExploration()->getDaedalus()->getLanguage();
        if ($event->hasTag('always_successful_thanks_to_pilot')) {
            $alwaysSuccessfulThanksToSkill = $this->translationService->translate(
                key: 'always_successful_thanks_to_skill',
                parameters: ['skill' => SkillEnum::PILOT->toString()],
                domain: 'planet_sector_event',
                language: $language,
            );

            return '////' . $alwaysSuccessfulThanksToSkill;
        }

        return null;
    }
}
