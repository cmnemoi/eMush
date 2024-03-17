<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\Planet;
use Mush\Exploration\Entity\PlanetName;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ExplorationNormalizerCest extends AbstractExplorationTester
{
    private ExplorationNormalizer $explorationNormalizer;
    private NormalizerInterface $normalizer;
    private StatusServiceInterface $statusService;

    private Planet $planet;
    private Exploration $exploration;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->explorationNormalizer = $I->grabService(ExplorationNormalizer::class);
        $this->normalizer = $I->grabService(NormalizerInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->explorationNormalizer->setNormalizer($this->normalizer);

        // given Chun is a pilot so landing is always successful
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::POC_PILOT_SKILL,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given a planet
        $this->planet = $this->createPlanet(
            sectors: [PlanetSectorEnum::OXYGEN],
            functionalTester: $I
        );
        $this->planet->setName(
            (new PlanetName())->setFirstSyllable(1)->setFourthSyllable(1)
        );

        // given an exploration
        $this->exploration = $this->createExploration(
            planet: $this->planet,
            explorators: new PlayerCollection([$this->chun]),
        );
    }

    public function testNormalize(FunctionalTester $I): void
    {
        // when the exploration is normalized for Chun
        $normalizedExploration = $this->explorationNormalizer->normalize(
            $this->exploration,
            format: null,
            context: ['currentPlayer' => $this->chun]
        );

        // then the exploration is normalized as expected
        $I->assertEquals(
            expected: [
                'createdAt' => $this->exploration->getCreatedAt(),
                'updatedAt' => $this->exploration->getUpdatedAt(),
                'cycleLength' => 10,
                'planet' => [
                    'id' => $this->planet->getId(),
                    'name' => 'Fugunys',
                    'orientation' => $this->planet->getOrientation(),
                    'distance' => $this->planet->getDistance(),
                    'sectors' => [
                        [
                            'id' => $this->planet->getSectors()->first()->getId(),
                            'key' => PlanetSectorEnum::UNKNOWN,
                            'name' => '???',
                            'description' => 'Caractéristique inconnue.',
                            'isVisited' => false,
                        ],
                    ],
                    'actions' => [],
                    'imageId' => 3,
                ],
                'explorators' => [
                    [
                        'key' => CharacterEnum::CHUN,
                        'name' => 'Chun',
                        'healthPoints' => 14,
                        'isDead' => false,
                        'isLost' => false,
                        'isStuck' => false,
                    ],
                ],
                'logs' => [
                    [
                        'id' => $this->exploration->getClosedExploration()->getLogs()->first()->getId(),
                        'planetSectorKey' => PlanetSectorEnum::LANDING,
                        'planetSectorName' => 'Atterrissage',
                        'eventName' => 'Rien à signaler',
                        'eventDescription' => 'L\'atterrissage se passe parfaitement bien, rien à signaler !',
                        'eventOutcome' => 'La zone est explorée, rien à signaler.////Toujours réussi car l\'expédition possède la compétence : Pilote.',
                    ],
                ],
                'estimated_duration' => 'Retour estimé dans 10 min.',
                'timer' => [
                    'name' => 'Prochain cycle',
                    'description' => 'Votre montre incassable affiche le temps qu\'il reste avant le prochain **Cycle**.//Vous gagnerez alors quelques précieux :pa::pm: selon votre état de santé.',
                    'timerCycle' => (clone $this->exploration->getUpdatedAt())->modify('+10 minutes')->format(\DateTimeInterface::ATOM),
                ],
                'uiElements' => [
                    'tips' => 'L\'exploration se déroule automatiquement. Toutes les 10 minutes, une nouvelle étape se déroule. Une fois parti, impossible de faire demi-tour.',
                    'recoltedInfos' => 'Infos récoltées...',
                    'newStep' => 'Nouvelle étape',
                    'lost' => 'Vous êtes perdue sur cette planète. Votre moral va rapidement décroitre... Implorez l\'équipage pour qu\'il vienne vous chercher.',
                ],
            ],
            actual: $normalizedExploration
        );
    }

    public function testNormalizeForLostPlayer(FunctionalTester $I): void
    {
        // given Chun is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->chun,
            tags: [],
            time: new \DateTime()
        );

        // given the exploration is finished
        $closedExploration = $this->exploration->getClosedExploration();
        $this->explorationService->closeExploration($this->exploration, []);

        // when the dummy exploration is normalized for Chun
        $dummyExploration = $this->explorationService->getDummyExplorationForLostPlayer($closedExploration);
        $normalizedExploration = $this->explorationNormalizer->normalize(
            $dummyExploration,
            format: null,
            context: ['currentPlayer' => $this->chun]
        );

        // then the exploration is normalized as expected
        $I->assertEquals(
            expected: [
                'createdAt' => $dummyExploration->getCreatedAt(),
                'updatedAt' => $dummyExploration->getUpdatedAt(),
                'cycleLength' => 10,
                'planet' => [
                    'id' => $this->planet->getId(),
                    'name' => 'Fugunys',
                    'orientation' => $this->planet->getOrientation(),
                    'distance' => $this->planet->getDistance(),
                    'sectors' => [
                        [
                            'id' => $this->planet->getSectors()->first()->getId(),
                            'key' => PlanetSectorEnum::UNKNOWN,
                            'name' => '???',
                            'description' => 'Caractéristique inconnue.',
                            'isVisited' => false,
                        ],
                    ],
                    'actions' => [],
                    'imageId' => 3,
                ],
                'explorators' => [
                    [
                        'key' => CharacterEnum::CHUN,
                        'name' => 'Chun',
                        'healthPoints' => 14,
                        'isDead' => false,
                        'isLost' => true,
                        'isStuck' => false,
                    ],
                ],
                'logs' => [
                    [
                        'id' => $dummyExploration->getClosedExploration()->getLogs()->first()->getId(),
                        'planetSectorKey' => PlanetSectorEnum::LANDING,
                        'planetSectorName' => 'Atterrissage',
                        'eventName' => 'Rien à signaler',
                        'eventDescription' => 'L\'atterrissage se passe parfaitement bien, rien à signaler !',
                        'eventOutcome' => 'La zone est explorée, rien à signaler.////Toujours réussi car l\'expédition possède la compétence : Pilote.',
                    ],
                ],
                'estimated_duration' => 'Expédition déjà terminée.',
                'timer' => [
                    'name' => 'Prochain cycle',
                    'description' => 'Votre montre incassable affiche le temps qu\'il reste avant le prochain **Cycle**.//Vous gagnerez alors quelques précieux :pa::pm: selon votre état de santé.',
                    'timerCycle' => null,
                ],
                'uiElements' => [
                    'tips' => 'Cette expédition est déjà terminée. Vous pouvez revoir ici les différents évènements qu\'ont rencontrés les membres de l\'équipage.',
                    'recoltedInfos' => 'Infos récoltées...',
                    'newStep' => 'Nouvelle étape',
                    'lost' => 'Vous êtes perdue sur cette planète. Votre moral va rapidement décroitre... Implorez l\'équipage pour qu\'il vienne vous chercher.',
                ],
            ],
            actual: $normalizedExploration
        );
    }
}
