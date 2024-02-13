<?php

declare(strict_types=1);

namespace Mush\tests\functional\Exploration\Normalizer;

use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Normalizer\ExplorationLogNormalizer;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class ExplorationLogNormalizerCest extends AbstractExplorationTester
{
    private ExplorationLogNormalizer $explorationLogNormalizer;

    private Exploration $exploration;
    private StatusServiceInterface $statusService;
    private TranslationServiceInterface $translationService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->explorationLogNormalizer = $I->grabService(ExplorationLogNormalizer::class);

        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->translationService = $I->grabService(TranslationServiceInterface::class);
    }

    public function testNormalizeLandingNothingToReportEventWithPilot(FunctionalTester $I): void
    {
        // given explorator is a pilot
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::POC_PILOT_SKILL,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );

        // when landing nothing to report event exploration log is normalized
        $explorationLog = $this->exploration->getClosedExploration()->getLogs()->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::LANDING,
                'planetSectorName' => 'Atterrissage',
                'eventName' => 'Rien à signaler',
                'eventDescription' => 'L\'atterrissage se passe parfaitement bien, rien à signaler !',
                'eventOutcome' => 'La zone est explorée, rien à signaler.////Toujours réussi car l\'expédition possède la compétence : Pilote.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeLandingNothingToReportEventWithoutAPilot(FunctionalTester $I): void
    {
        // given landing sector has only nothing to report event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::LANDING,
            events: [PlanetSectorEvent::NOTHING_TO_REPORT => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );

        // when landing nothing to report event exploration log is normalized
        $explorationLog = $this->exploration->getClosedExploration()->getLogs()->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::LANDING,
                'planetSectorName' => 'Atterrissage',
                'eventName' => 'Rien à signaler',
                'eventDescription' => 'L\'atterrissage se passe parfaitement bien, rien à signaler !',
                'eventOutcome' => 'La zone est explorée, rien à signaler.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeTiredEvent(FunctionalTester $I): void
    {
        // given desert sector has only tired event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::DESERT,
            events: [PlanetSectorEvent::TIRED_2 => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::DESERT, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );

        // given two extra steps are made to trigger the tired event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when tired event exploration log is normalized
        $explorationLog = $this->exploration->getClosedExploration()->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getPlanetSectorName() === PlanetSectorEnum::DESERT,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::DESERT,
                'planetSectorName' => 'Désert',
                'eventName' => 'Fatigue',
                'eventDescription' => 'La marche dans cette étendue désertique est pénible et très douloureuse.',
                'eventOutcome' => 'Tous les équipiers subissent 2 points de dégâts.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeArtefactEvent(FunctionalTester $I): void
    {
        // given intelligent life sector has only artefact event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: [PlanetSectorEvent::ARTEFACT => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );

        // given two extra steps are made to trigger the artefact event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when artefact event exploration log is normalized
        /** @var ExplorationLog $explorationLog */
        $explorationLog = $this->exploration->getClosedExploration()->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getPlanetSectorName() === PlanetSectorEnum::INTELLIGENT,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);
        
        $lootedArtefact = $this->translationService->translate(
            key: $explorationLog->getParameters()['target_equipment'],
            parameters: [],
            domain: 'items',
            language: $this->exploration->getDaedalus()->getLanguage(),
        );

        $maleExpectedEventDescription = "Derrière un rocher, vous trouvez une créature étrange très affaiblie. Vous lui donnez un peu d'eau afin qu'elle reprenne connaissance. La créature vous offre un {$lootedArtefact} avant de reprendre sa route.";
        $femaleExpectedEventDescription = "Derrière un rocher, vous trouvez une créature étrange très affaiblie. Vous lui donnez un peu d'eau afin qu'elle reprenne connaissance. La créature vous offre une {$lootedArtefact} avant de reprendre sa route.";

        // then exploration log is normalized as expected
        try {
            $I->assertEquals(
                expected: [
                    'id' => $explorationLog->getId(),
                    'planetSectorKey' => PlanetSectorEnum::INTELLIGENT,
                    'planetSectorName' => 'Vie intelligente',
                    'eventName' => 'Artefact',
                    'eventDescription' => $maleExpectedEventDescription,
                    'eventOutcome' => 'Vous trouvez un artefact.',
                ],
                actual: $normalizedExplorationLog,
            );
        } catch (\Exception $e) {
            $I->assertEquals(
                expected: [
                    'id' => $explorationLog->getId(),
                    'planetSectorKey' => PlanetSectorEnum::INTELLIGENT,
                    'planetSectorName' => 'Vie intelligente',
                    'eventName' => 'Artefact',
                    'eventDescription' => $femaleExpectedEventDescription,
                    'eventOutcome' => 'Vous trouvez un artefact.',
                ],
                actual: $normalizedExplorationLog,
            );
        }
    }
}
