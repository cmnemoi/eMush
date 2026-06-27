<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Equipment\Enum\ItemEnum;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Project\Enum\ProjectName;
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

    protected function getLogParameters(PlanetSectorEvent $event): array
    {
        $logParameters = parent::getLogParameters($event);
        $logParameters['fight_prevented_by_item'] = null;
        $logParameters['fight_prevented_by_skill'] = null;

        if ($event->getConfig()->getName() === PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT) {
            if ($event->getExploration()->hasAnActiveDiplomat()) {
                $logParameters['fight_prevented_by_skill'] = '////' . $this->translationService->translate(
                    key: 'fight_prevented_by_skill',
                    parameters: ['skill' => SkillEnum::DIPLOMAT->toString(), 'character_gender' => 'other'],
                    domain: 'planet_sector_event',
                    language: $event->getExploration()->getDaedalus()->getLanguage()
                );
            }
            if ($event->getExploration()->hasAWhiteFlag()) {
                $logParameters['fight_prevented_by_item'] = '////' . $this->translationService->translate(
                    key: 'fight_prevented_by_item',
                    parameters: ['item' => ItemEnum::WHITE_FLAG],
                    domain: 'planet_sector_event',
                    language: $event->getExploration()->getDaedalus()->getLanguage()
                );
            }
        }

        return $logParameters;
    }

    private function getAlwaysSuccessfulThanksToSkillLogParameter(PlanetSectorEvent $event): ?string
    {
        $language = $event->getExploration()->getDaedalus()->getLanguage();
        if ($event->hasTag('always_successful_thanks_to_pilot')) {
            $alwaysSuccessfulThanksToSkill = $this->translationService->translate(
                key: 'always_successful_thanks_to_skill',
                parameters: ['skill' => SkillEnum::PILOT->toString(), 'character_gender' => 'other'],
                domain: 'planet_sector_event',
                language: $language,
            );

            return '////' . $alwaysSuccessfulThanksToSkill;
        }

        if ($event->hasTag('always_successful_thanks_to_project')) {
            $alwaysSuccessfulThanksToProject = $this->translationService->translate(
                key: 'always_successful_thanks_to_project',
                parameters: ['project' => ProjectName::ICARUS_ANTIGRAV_PROPELLER->value],
                domain: 'planet_sector_event',
                language: $language,
            );

            return '////' . $alwaysSuccessfulThanksToProject;
        }

        return null;
    }
}
