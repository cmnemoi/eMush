<?php

declare(strict_types=1);

namespace Mush\tests\functional\Exploration\Normalizer;

use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Normalizer\ExplorationLogNormalizer;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class ExplorationLogNormalizerCest extends AbstractExplorationTester
{
    private ExplorationLogNormalizer $explorationLogNormalizer;

    private Exploration $exploration;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->explorationLogNormalizer = $I->grabService(ExplorationLogNormalizer::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
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
        $this->exploration = $this->createExploration($this->createPlanet([PlanetSectorEnum::OXYGEN], $I));

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
                'eventOutcome' => 'La zone est explorée, rien à signaler.//Toujours réussi car l\'expédition possède la compétence : Pilote.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeLandingNothingToReportEventWithoutAPilot(FunctionalTester $I): void
    {
        // given landing sector has only nothing to report event
        /** @var PlanetSectorConfig $landingSectorConfig */
        $landingSectorConfig = $this->daedalus->getGameConfig()->getPlanetSectorConfigs()->filter(
            fn (PlanetSectorConfig $planetSectorConfig) => $planetSectorConfig->getSectorName() === PlanetSectorEnum::LANDING,
        )->first();
        $landingSectorConfig->setExplorationEvents([PlanetSectorEvent::NOTHING_TO_REPORT => 1]);

        // given exploration is created
        $this->exploration = $this->createExploration($this->createPlanet([PlanetSectorEnum::OXYGEN], $I));

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
}
