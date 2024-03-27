<?php

declare(strict_types=1);

namespace Mush\tests\functional\Exploration\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Entity\PlanetSectorEventConfig;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Exploration\Normalizer\ExplorationLogNormalizer;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

final class ExplorationLogNormalizerCest extends AbstractExplorationTester
{
    private ExplorationLogNormalizer $explorationLogNormalizer;

    private Exploration $exploration;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->explorationLogNormalizer = $I->grabService(ExplorationLogNormalizer::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
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

        // given only starmap can be looted from the artefact event
        /** @var PlanetSectorEventConfig $eventConfig */
        $eventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => PlanetSectorEvent::ARTEFACT]);
        $eventConfig->setOutputTable([ItemEnum::STARMAP_FRAGMENT => 1]);

        // given two extra steps are made to trigger the artefact event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when artefact event exploration log is normalized
        /** @var ExplorationLog $explorationLog */
        $explorationLog = $this->exploration->getClosedExploration()->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getPlanetSectorName() === PlanetSectorEnum::INTELLIGENT,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::INTELLIGENT,
                'planetSectorName' => 'Vie intelligente',
                'eventName' => 'Artefact',
                'eventDescription' => "Derrière un rocher, vous trouvez une créature étrange très affaiblie. Vous lui donnez un peu d'eau afin qu'elle reprenne connaissance. La créature vous offre un Morceau de carte stellaire avant de reprendre sa route.",
                'eventOutcome' => 'Vous trouvez un artefact.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeArtefactEventWithBabelModule(FunctionalTester $I): void
    {
        // given intelligent life sector has only artefact event
        $intelligentSector = $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: [PlanetSectorEvent::ARTEFACT => 1]
        );

        // given Chun has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun has a babel module
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::BABEL_MODULE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: $this->players,
        );

        // given only starmap can be looted from the artefact event
        /** @var PlanetSectorEventConfig $eventConfig */
        $eventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => PlanetSectorEvent::ARTEFACT]);
        $eventConfig->setOutputTable([ItemEnum::STARMAP_FRAGMENT => 1]);

        // given two extra steps are made to trigger the artefact event
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when artefact event exploration log is normalized
        /** @var ExplorationLog $explorationLog */
        $explorationLog = $this->exploration->getClosedExploration()->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::ARTEFACT,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::INTELLIGENT,
                'planetSectorName' => 'Vie intelligente',
                'eventName' => 'Artefact',
                'eventDescription' => "Derrière un rocher, vous trouvez une créature étrange très affaiblie. Vous lui donnez un peu d'eau afin qu'elle reprenne connaissance. La créature vous offre un Morceau de carte stellaire avant de reprendre sa route.",
                'eventOutcome' => 'Vous trouvez un artefact.////+100% Module Babel',
            ],
            actual: $normalizedExplorationLog,
        );

        // then intelligent sector events are the same as before
        $I->assertEquals(
            expected: [PlanetSectorEvent::ARTEFACT => 1],
            actual: $intelligentSector->getExplorationEvents()->toArray(),
        );
    }

    public function testNormalizeArtefactEventTranslationForRuins(FunctionalTester $I): void
    {
        // given ruins sector has an artefact event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::RUINS,
            events: [PlanetSectorEvent::ARTEFACT => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::RUINS, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );

        // given babel module can be looted from the artefact event
        /** @var PlanetSectorEventConfig $eventConfig */
        $eventConfig = $I->grabEntityFromRepository(PlanetSectorEventConfig::class, ['name' => PlanetSectorEvent::ARTEFACT]);
        $eventConfig->setOutputTable([ItemEnum::BABEL_MODULE => 1]);

        // given two extra steps are made to trigger the artefact event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when artefact event exploration log is normalized
        /** @var ExplorationLog $explorationLog */
        $explorationLog = $this->exploration->getClosedExploration()->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getPlanetSectorName() === PlanetSectorEnum::RUINS,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::RUINS,
                'planetSectorName' => 'Ruines',
                'eventName' => 'Artefact',
                'eventDescription' => 'Au sein de la ruine du plus grand bâtiment de la cité vous trouvez un artefact alien intact !',
                'eventOutcome' => 'Vous trouvez un artefact. ////+100% Module Babel',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeKillRandomEvent(FunctionalTester $I): void
    {
        // given sismic activity sector has only kill random event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SISMIC_ACTIVITY,
            events: [PlanetSectorEvent::KILL_RANDOM => 1]
        );

        // given player has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::SISMIC_ACTIVITY], $I),
            explorators: new ArrayCollection([$this->player]),
        );

        // given kill random event is triggered
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when kill random event exploration log is normalized
        $explorationLog = $this->exploration->getClosedExploration()->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getPlanetSectorName() === PlanetSectorEnum::SISMIC_ACTIVITY,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::SISMIC_ACTIVITY,
                'planetSectorName' => 'Sismique',
                'eventName' => 'Mort',
                'eventDescription' => 'Une faille s\'ouvre sous les pieds de l\'expédition !!! Chun glisse et disparaît dans un cri d\'effroi !',
                'eventOutcome' => 'Un équipier meurt.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeKillAllEvent(FunctionalTester $I): void
    {
        // given sismic activity sector has only kill all event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::VOLCANIC_ACTIVITY,
            events: [PlanetSectorEvent::KILL_ALL => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::VOLCANIC_ACTIVITY, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the kill all event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        if (!$closedExploration->isExplorationFinished()) {
            $this->explorationService->dispatchExplorationEvent($this->exploration);
        }

        // when kill all event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getPlanetSectorName() === PlanetSectorEnum::VOLCANIC_ACTIVITY,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::VOLCANIC_ACTIVITY,
                'planetSectorName' => 'Volcan',
                'eventName' => 'Mort du groupe',
                'eventDescription' => 'Alors que la montagne la plus proche de l\'expédition se met à cracher un jet de lave à plus de 500m de hauteur, le sol s\'effondre sous leurs pieds et les engloutit dans un déluge de flammes.',
                'eventOutcome' => 'Tous les équipiers meurent.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeFightEvent(FunctionalTester $I): void
    {
        // given intelligent life has only fight event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: [PlanetSectorEvent::FIGHT_12 => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the fight event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when fight exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::FIGHT,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::INTELLIGENT,
                'planetSectorName' => 'Vie intelligente',
                'eventName' => 'Combat',
                'eventDescription' => 'Un être étrange s\'approche de vous et lance de grands cris aigus qui vous cassent les oreilles. Il va falloir le faire taire.',
                'eventOutcome' => 'Vous affrontez une créature.////Force Créature : 12////Force Équipe : 2////L\'équipe subit 10 points de dégâts.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeFightEventWithAWhiteFlag(FunctionalTester $I): void
    {
        // given intelligent life has only fight and provision events
        $intelligentSector = $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: [
                PlanetSectorEvent::FIGHT_12 => PHP_INT_MAX - 1,
                PlanetSectorEvent::PROVISION_2 => 1,
            ]
        );

        // given Chun has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given Chun has a white flag
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::WHITE_FLAG,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I),
            explorators: new PlayerCollection([$this->chun]),
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given fight event is triggered
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when intelligent sector event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getPlanetSectorName() === PlanetSectorEnum::INTELLIGENT,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected : provision event as Chun has a white flag which prevents the fight event
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::INTELLIGENT,
                'planetSectorName' => 'Vie intelligente',
                'eventName' => 'Provision',
                'eventDescription' => 'Alors que l\'expédition progresse tranquillement vous tombez nez à nez avec un être étrange. Impossible de communiquer avec lui mais avant de partir, il vous donne un sac qui contient du gibier alien.',
                'eventOutcome' => 'Vous gagnez 2 Steaks aliens.////Probabilité de combat annulée Drapeau blanc',
            ],
            actual: $normalizedExplorationLog,
        );

        // then intelligent sector events still have the same probabilities
        $I->assertEquals(
            expected: [
                PlanetSectorEvent::FIGHT_12 => PHP_INT_MAX - 1,
                PlanetSectorEvent::PROVISION_2 => 1,
            ],
            actual: $intelligentSector->getExplorationEvents()->toArray(),
        );
    }

    public function testNormalizeFightEventWithExpeditionStrengthSuperiorToCreatureStrength(FunctionalTester $I): void
    {
        // given intelligent life has only fight event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: ['fight_1' => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the fight event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when fight exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::FIGHT,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::INTELLIGENT,
                'planetSectorName' => 'Vie intelligente',
                'eventName' => 'Combat',
                'eventDescription' => 'Un être étrange s\'approche de vous et lance de grands cris aigus qui vous cassent les oreilles. Il va falloir le faire taire.',
                'eventOutcome' => 'Vous affrontez une créature.////Force Créature : 1////Force Équipe : 2////Créature décède.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeProvisionEventFourSteaks(FunctionalTester $I): void
    {
        // given ruminant sector has only provision event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::RUMINANT,
            events: [PlanetSectorEvent::PROVISION_4 => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::RUMINANT, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the kill all event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when kill all event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::PROVISION
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::RUMINANT,
                'planetSectorName' => 'Ruminants',
                'eventName' => 'Provision',
                'eventDescription' => 'Vous chassez avec succès un Chab Chab... Vous récupérez de la viande alien.',
                'eventOutcome' => 'Vous gagnez 4 Steaks aliens.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeProvisionEventTwoSteaks(FunctionalTester $I): void
    {
        // given ruminant sector has only provision event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::RUMINANT,
            events: [PlanetSectorEvent::PROVISION_2 => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::RUMINANT, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the kill all event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when kill all event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::PROVISION
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::RUMINANT,
                'planetSectorName' => 'Ruminants',
                'eventName' => 'Provision',
                'eventDescription' => 'Vous rencontrez une myriade de petits rongeurs. Dans la panique générale vous parvenez à attraper l\'un d\'entre eux.',
                'eventOutcome' => 'Vous gagnez 2 Steaks aliens.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeHarvestEvent(FunctionalTester $I): void
    {
        // given forest sector has only harvest event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::FOREST,
            events: [PlanetSectorEvent::HARVEST_2 => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::FOREST, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the harvest event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when harvest event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::HARVEST
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::FOREST,
                'planetSectorName' => 'Forêt',
                'eventName' => 'Récolte',
                'eventDescription' => 'Vous trouvez 2 fruits qui ont l\'air délicieux. Mieux vaut les ramener au vaisseau et les analyser avant de les manger…',
                'eventOutcome' => 'Vous gagnez 2 Fruits aliens.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeHarvestEventInFruitTreesSector(FunctionalTester $I): void
    {
        // given fruit trees sector has only harvest event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::FRUIT_TREES,
            events: [PlanetSectorEvent::HARVEST_1 => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::FRUIT_TREES, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the harvest event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when harvest event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::HARVEST
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::FRUIT_TREES,
                'planetSectorName' => 'Vergers',
                'eventName' => 'Récolte',
                'eventDescription' => 'Plusieurs arbustes touffus attirent votre attention, dans l\'un d\'entre eux se trouve de curieux fruits…',
                'eventOutcome' => 'Vous gagnez 1 Fruit alien.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeDiseaseEvent(FunctionalTester $I): void
    {
        // given forest sector has only disease event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::FOREST,
            events: [PlanetSectorEvent::DISEASE => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::FOREST, PlanetSectorEnum::OXYGEN], $I),
            explorators: new ArrayCollection([$this->player]),
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the disease event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when disease event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::DISEASE,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::FOREST,
                'planetSectorName' => 'Forêt',
                'eventName' => 'Maladie',
                'eventDescription' => 'Une liane gluante frôle la joue de Chun.',
                'eventOutcome' => 'Un équipier tombe malade.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeStarmapEvent(FunctionalTester $I): void
    {
        // given cristal field sector has only provision event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::CRISTAL_FIELD,
            events: [PlanetSectorEvent::STARMAP => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::CRISTAL_FIELD, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the starmap event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when starmap event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::STARMAP
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::CRISTAL_FIELD,
                'planetSectorName' => 'Cristalite',
                'eventName' => 'Éclat de Carte',
                'eventDescription' => 'Ce champ de cristalite est en activité. Au centre de l\'atrium principal, un éclat baignant dans des eaux métalliques fait converger des faisceaux de lumières aux couleurs inconnues. Allez hop, on l\'embarque.',
                'eventOutcome' => 'Vous trouvez 1 éclat de carte stellaire en cristalite.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeMushTrap(FunctionalTester $I): void
    {
        // given cristal field sector has only mush trap event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::CRISTAL_FIELD,
            events: [PlanetSectorEvent::MUSH_TRAP => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::CRISTAL_FIELD, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the mush trap event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when mush trap event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::MUSH_TRAP
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::CRISTAL_FIELD,
                'planetSectorName' => 'Cristalite',
                'eventName' => 'Piège Mush',
                'eventDescription' => 'Ces champs ont été visités récemment. Vous avancez mais… une odeur de moisi vous saisit à la gorge, des volutes roses vous asphyxient, c\'est un piège ! Fuyez, le Mush est déjà là !',
                'eventOutcome' => 'Tous les équipiers risquent une infection Mush.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeAgainEvent(FunctionalTester $I): void
    {
        // given desert sector has only again event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::DESERT,
            events: [PlanetSectorEvent::AGAIN => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::DESERT, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the kill all event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when kill all event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::AGAIN
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::DESERT,
                'planetSectorName' => 'Désert',
                'eventName' => 'Érrance',
                'eventDescription' => 'Cette marche dans le désert ne rime à rien, vous n\'avez aucune idée de votre position et décidez de revenir sur vos pas.',
                'eventOutcome' => 'Échec de l\'exploration de la zone. Il reste quand même des choses à découvrir…',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeItemLostEvent(FunctionalTester $I): void
    {
        // given inelligent sector has only item lost event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: [PlanetSectorEvent::ITEM_LOST => 1]
        );

        // given player has a iTrackie
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::ITRACKIE,
            equipmentHolder: $this->player,
            reasons : [],
            time: new \DateTime(),
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the item lost event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when item lost event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::ITEM_LOST
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::INTELLIGENT,
                'planetSectorName' => 'Vie intelligente',
                'eventName' => 'Objet perdu',
                'eventDescription' => 'Un être étrange tente de rentrer en contact avec vous, il inspecte chacun d\'entre vous puis s\'empare d\'un iTrackie®© appartenant à Chun et s\'enfuit à grandes enjambées.',
                'eventOutcome' => 'Un objet possédé par un des équipiers est perdu.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeItemLostEventWithNoItemToLose(FunctionalTester $I): void
    {
        // given inelligent sector has only item lost event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::INTELLIGENT,
            events: [PlanetSectorEvent::ITEM_LOST => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::INTELLIGENT, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the item lost event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when intelligent sector event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getPlanetSectorName() === PlanetSectorEnum::INTELLIGENT,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected : nothing to report
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::INTELLIGENT,
                'planetSectorName' => 'Vie intelligente',
                'eventName' => 'Rien à signaler',
                'eventDescription' => 'Un grand cri résonne. Un moment de panique. Puis plus rien…',
                'eventOutcome' => 'La zone est explorée, rien à signaler.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeBackEvent(FunctionalTester $I): void
    {
        // given sismic activity sector has only back event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SISMIC_ACTIVITY,
            events: [PlanetSectorEvent::BACK => 1]
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::SISMIC_ACTIVITY, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the back event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        if (!$closedExploration->isExplorationFinished()) {
            $this->explorationService->dispatchExplorationEvent($this->exploration);
        }

        // when back event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::BACK
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::SISMIC_ACTIVITY,
                'planetSectorName' => 'Sismique',
                'eventName' => 'Retour',
                'eventDescription' => 'Une violente secousse paralyse l\'expédition… Puis de nouveau le silence… Mieux vaut revenir au Daedalus vite fait !',
                'eventOutcome' => 'Vous abandonnez l\'expédition en cours.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizePlayerLostEvent(FunctionalTester $I): void
    {
        // given cristal field sector has only player lost event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::CRISTAL_FIELD,
            events: [PlanetSectorEvent::PLAYER_LOST => 1]
        );

        // given MIA sector has only nothing to report event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::LOST,
            events: [PlanetSectorEvent::NOTHING_TO_REPORT => 1]
        );

        // given player has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->player,
            reasons : [],
            time: new \DateTime(),
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::CRISTAL_FIELD], $I),
            explorators: new ArrayCollection([$this->player]),
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the player lost event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when player lost event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::PLAYER_LOST
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::CRISTAL_FIELD,
                'planetSectorName' => 'Cristalite',
                'eventName' => 'Perdu',
                'eventDescription' => 'Ces champs sont un vrai labyrinthe… Bon y a rien à secouer ici, on bouge. Est-ce que quelqu\'un a vu Chun ?',
                'eventOutcome' => 'Un équipier se perd.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeFindLostEvent(FunctionalTester $I): void
    {
        // given Lost sector only has find lost event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::LOST,
            events: [PlanetSectorEvent::FIND_LOST => 1]
        );

        // given player is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::LOST, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the find lost event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when find lost event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::FIND_LOST
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $firstVersion = 'Vous avez trouvé des traces de pas humaines !!! En les suivant vous tombez sur Chun. Quelle déception…';
        $secondVersion = 'Alors que vous vous apprétiez à quitter la zone, vous entendez des cris derrière vous. Il s\'agit de Chun qui court après vous en hurlant depuis plus d\'une heure. Ses vêtements sont tout déchirés !';

        $I->assertEquals(expected: 'Perdu', actual: $normalizedExplorationLog['planetSectorName']);
        $I->assertEquals(expected: 'Retrouvaille', actual: $normalizedExplorationLog['eventName']);
        $I->assertEquals(expected: 'Un équipier perdu est retrouvé !', actual: $normalizedExplorationLog['eventOutcome']);
        $I->assertContains(
            needle: $normalizedExplorationLog['eventDescription'],
            haystack: [$firstVersion, $secondVersion],
        );
    }

    public function testNormalizeKillLostEvent(FunctionalTester $I): void
    {
        // given Lost sector only has kill lost event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::LOST,
            events: [PlanetSectorEvent::KILL_LOST => 1]
        );

        // given player is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::LOST, PlanetSectorEnum::OXYGEN], $I),
            explorators: $this->players,
        );
        $closedExploration = $this->exploration->getClosedExploration();

        // given two extra steps are made to trigger the find lost event
        $this->explorationService->dispatchExplorationEvent($this->exploration);
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when kill lost event exploration log is normalized
        $explorationLog = $closedExploration->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::KILL_LOST
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected

        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::LOST,
                'planetSectorName' => 'Perdu',
                'eventName' => 'Mort',
                'eventDescription' => 'Vous avez trouvé Chun derrière un rocher ! Malheureusement, elle ne bouge plus du tout. D\'ailleurs, elle n\'a pas l\'air de respirer non plus. A tous les coups, les gars du labo vont vous dire qu\'elle est morte…',
                'eventOutcome' => 'L\'équipier perdu meurt.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeAccidentEventWithARope(FunctionalTester $I): void
    {
        // given sismic activity sector has only accident event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::SISMIC_ACTIVITY,
            events: [PlanetSectorEvent::ACCIDENT_3_5 => 1]
        );

        // given player has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // given player has a rope
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::ROPE,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::SISMIC_ACTIVITY], $I),
            explorators: new ArrayCollection([$this->player]),
        );

        // given accident is triggered
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when accident exploration log is normalized
        $explorationLog = $this->exploration->getClosedExploration()->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getPlanetSectorName() === PlanetSectorEnum::SISMIC_ACTIVITY,
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::SISMIC_ACTIVITY,
                'planetSectorName' => 'Sismique',
                'eventName' => 'Accident',
                'eventDescription' => 'Chun chute dans une crevasse… Aïe !////Esquivé : Corde',
                'eventOutcome' => 'Un équipier subit entre 3 et 5 points de dégâts.',
            ],
            actual: $normalizedExplorationLog,
        );
    }

    public function testNormalizeFuelEventWithADrill(FunctionalTester $I): void
    {
        // given hydrocarbon sector has only fuel event
        $this->setupPlanetSectorEvents(
            sectorName: PlanetSectorEnum::HYDROCARBON,
            events: [PlanetSectorEvent::FUEL_6 => 1]
        );

        // given player has a spacesuit
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: GearItemEnum::SPACESUIT,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // given player has a drill
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::DRILL,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );

        // given exploration is created
        $this->exploration = $this->createExploration(
            planet: $this->createPlanet([PlanetSectorEnum::HYDROCARBON], $I),
            explorators: new ArrayCollection([$this->player]),
        );

        // given fuel event is triggered
        $this->explorationService->dispatchExplorationEvent($this->exploration);

        // when fuel event exploration log is normalized
        $explorationLog = $this->exploration->getClosedExploration()->getLogs()->filter(
            fn (ExplorationLog $explorationLog) => $explorationLog->getEventName() === PlanetSectorEvent::FUEL
        )->first();
        $normalizedExplorationLog = $this->explorationLogNormalizer->normalize($explorationLog);

        // then exploration log is normalized as expected
        $I->assertEquals(
            expected: [
                'id' => $explorationLog->getId(),
                'planetSectorKey' => PlanetSectorEnum::HYDROCARBON,
                'planetSectorName' => 'Hydrocarbures',
                'eventName' => 'Hydrocarbures',
                'eventDescription' => 'L\'expédition trouve un petit lac d\'heptanol violacé ! Avec tout ça le Daedalus n\'est pas près de tomber en panne !',
                'eventOutcome' => 'Vous gagnez 12 unités de Fuel.////x2 car l\'expédition dispose de l\'objet : Foreuse',
            ],
            actual: $normalizedExplorationLog,
        );
    }
}
