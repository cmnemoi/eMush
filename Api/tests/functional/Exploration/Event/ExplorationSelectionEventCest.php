<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Exploration\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\ExplorationSelectionEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Modifier\Dto\ExplorationEventModifierConfigDto;
use Mush\Modifier\Entity\Config\ExplorationEventModifierConfig;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Entity\ModifierProviderInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierPriorityEnum;
use Mush\Modifier\Enum\ModifierStrategyEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;

// I only test here Player, Disease, Status and Items. Other modifier would not make sense. (The Daedalus is always operative or a rebel base should not care about being in an exploration or not.)
final class ExplorationSelectionEventCest extends AbstractExplorationTester
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private StatusServiceInterface $statusService;
    private ModifierCreationServiceInterface $modifierCreationService;

    private Exploration $exploration;
    private PlanetSector $sector;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->modifierCreationService = $I->grabService(ModifierCreationServiceInterface::class);

        // given Chun, Kuan-Ti, and Janice have a spacesuit
        foreach ([$this->chun, $this->kuanTi] as $player) {
            $this->createEquipment(
                equipmentName: GearItemEnum::SPACESUIT,
                holder: $player,
            );
        }

        $planet = $this->createPlanet([PlanetSectorEnum::INTELLIGENT], $I);
        $this->setupPlanetSectorEvents(PlanetSectorEnum::INTELLIGENT, [PlanetSectorEvent::FIGHT_ALIEN => 1]);
        $this->exploration = $this->createExploration($planet, new ArrayCollection([$this->chun]));
        $this->sector = $this->exploration->getNextSectorOrThrow();
    }

    public function explorationModifierShouldReplaceEventIfCriteriaIsEventNameAndEventNameCorrespond(FunctionalTester $I): void
    {
        // we create a config for a modifier that should replace a fight with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REPLACE, ExplorationEventModifierConfig::EVENT_NAME, PlanetSectorEvent::FIGHT, PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->chun->getPlace(), $this->chun, $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event replaced
        $I->assertEquals([PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT => 1], $collection->toArray());
    }

    public function explorationModifierShouldNotReplaceEventIfCriteriaIsEventNameAndEventNameDoNotCorrespond(FunctionalTester $I): void
    {
        // we create a config for a modifier that should replace a disaster with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REPLACE, ExplorationEventModifierConfig::EVENT_NAME, PlanetSectorEvent::DISASTER, PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->chun->getPlace(), $this->chun, $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event replaced
        $I->assertEquals([PlanetSectorEvent::FIGHT_ALIEN => 1], $collection->toArray());
    }

    public function explorationModifierShouldReplaceEventIfCriteriaIsNameAndNameCorrespond(FunctionalTester $I): void
    {
        // we create a config for a modifier that should replace a fight 12 with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REPLACE, ExplorationEventModifierConfig::NAME, PlanetSectorEvent::FIGHT_ALIEN, PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->chun->getPlace(), $this->chun, $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event replaced
        $I->assertEquals([PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT => 1], $collection->toArray());
    }

    public function explorationModifierShouldNotReplaceEventIfCriteriaIsNameAndNameDoNotCorrespond(FunctionalTester $I): void
    {
        // we create a config for a modifier that should replace a fight 32 with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REPLACE, ExplorationEventModifierConfig::EVENT_NAME, PlanetSectorEvent::FIGHT_MANKAROG, PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->chun->getPlace(), $this->chun, $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event replaced
        $I->assertEquals([PlanetSectorEvent::FIGHT_ALIEN => 1], $collection->toArray());
    }

    public function explorationModifierShouldRemoveEvent(FunctionalTester $I): void
    {
        // we create a config for a modifier that should replace a fight with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REMOVE, ExplorationEventModifierConfig::EVENT_NAME, PlanetSectorEvent::FIGHT, null, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->chun->getPlace(), $this->chun, $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event removed
        $I->assertEquals([], $collection->toArray());
    }

    public function explorationModifierShouldAddEvent(FunctionalTester $I): void
    {
        // we create a config for a modifier that should replace a fight with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::ADD, ExplorationEventModifierConfig::EVENT_NAME, null, PlanetSectorEvent::ARTEFACT, 5);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->chun->getPlace(), $this->chun, $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event removed
        $I->assertEquals([PlanetSectorEvent::FIGHT_ALIEN => 1, PlanetSectorEvent::ARTEFACT => 5], $collection->toArray());
    }

    public function explorationModifierShouldWorkIfProviderIsPlayerAndPlayerIsInExploration(FunctionalTester $I): void
    {
        // we create a config for a modifier that should replace a fight with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REPLACE, ExplorationEventModifierConfig::EVENT_NAME, PlanetSectorEvent::FIGHT, PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->chun->getPlace(), $this->chun, $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event replaced
        $I->assertEquals([PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT => 1], $collection->toArray());
    }

    public function explorationModifierShouldNotWorkIfProviderIsPlayerAndPlayerIsNotExploration(FunctionalTester $I): void
    {
        // we create a config for a modifier that should replace a fight with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REPLACE, ExplorationEventModifierConfig::EVENT_NAME, PlanetSectorEvent::FIGHT, PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->kuanTi->getPlace(), $this->kuanTi, $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event replaced
        $I->assertEquals([PlanetSectorEvent::FIGHT_ALIEN => 1], $collection->toArray());
    }

    public function explorationModifierShouldWorkIfProviderIsEquipmentAndPlayerIsInExploration(FunctionalTester $I): void
    {
        // we create a config for a modifier that should replace a fight with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REPLACE, ExplorationEventModifierConfig::EVENT_NAME, PlanetSectorEvent::FIGHT, PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->chun->getPlace(), $this->chun->getEquipmentByNameOrThrow(GearItemEnum::SPACESUIT), $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event replaced
        $I->assertEquals([PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT => 1], $collection->toArray());
    }

    public function explorationModifierShouldNotWorkIfProviderIsEquipmentAndPlayerIsNotExploration(FunctionalTester $I): void
    {
        // we create a config for a modifier that should replace a fight with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REPLACE, ExplorationEventModifierConfig::EVENT_NAME, PlanetSectorEvent::FIGHT, PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->kuanTi->getPlace(), $this->kuanTi->getEquipmentByNameOrThrow(GearItemEnum::SPACESUIT), $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event replaced
        $I->assertEquals([PlanetSectorEvent::FIGHT_ALIEN => 1], $collection->toArray());
    }

    public function explorationModifierShouldWorkIfProviderIsDiseaseAndPlayerIsInExploration(FunctionalTester $I): void
    {
        // given Chun is sick
        $disease = $this->playerDiseaseService->createDiseaseFromName(DiseaseEnum::BLACK_BITE->toString(), $this->chun);

        // we create a config for a modifier that should replace a fight with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REPLACE, ExplorationEventModifierConfig::EVENT_NAME, PlanetSectorEvent::FIGHT, PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->chun->getPlace(), $disease, $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event replaced
        $I->assertEquals([PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT => 1], $collection->toArray());
    }

    public function explorationModifierShouldWorkIfProviderIsDiseaseAndPlayerIsNotInExploration(FunctionalTester $I): void
    {
        // given Kuan Ti is sick
        $disease = $this->playerDiseaseService->createDiseaseFromName(DiseaseEnum::BLACK_BITE->toString(), $this->kuanTi);

        // we create a config for a modifier that should replace a fight with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REPLACE, ExplorationEventModifierConfig::EVENT_NAME, PlanetSectorEvent::FIGHT, PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->kuanTi->getPlace(), $disease, $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event replaced
        $I->assertEquals([PlanetSectorEvent::FIGHT_ALIEN => 1], $collection->toArray());
    }

    public function explorationModifierShouldWorkIfProviderIsStatusAndPlayerIsInExploration(FunctionalTester $I): void
    {
        // given Chun has a status
        $status = $this->createStatusOn(PlayerStatusEnum::CAT_OWNER, $this->player);

        // we create a config for a modifier that should replace a fight with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REPLACE, ExplorationEventModifierConfig::EVENT_NAME, PlanetSectorEvent::FIGHT, PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->chun->getPlace(), $status, $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event replaced
        $I->assertEquals([PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT => 1], $collection->toArray());
    }

    public function explorationModifierShouldWorkIfProviderIsStatusAndPlayerIsNotInExploration(FunctionalTester $I): void
    {
        // given Kuan Ti has a status
        $status = $this->createStatusOn(PlayerStatusEnum::CAT_OWNER, $this->player);

        // we create a config for a modifier that should replace a fight with a nothing to report.
        $config = $this->createModifierConfig(ModifierHolderClassEnum::DAEDALUS, ExplorationEventModifierConfig::REPLACE, ExplorationEventModifierConfig::EVENT_NAME, PlanetSectorEvent::FIGHT, PlanetSectorEvent::NOTHING_TO_REPORT_FIGHT, null);
        $I->haveInRepository($config);
        // we create a modifier with the player as the provider
        $this->createModifier($this->kuanTi->getPlace(), $status, $config);

        // when we trigger the selection event
        $collection = $this->explorationService->getPlanetSectorEventProbaCollection($this->sector, $this->exploration);

        // then we have a collection with the event replaced
        $I->assertEquals([PlanetSectorEvent::FIGHT_ALIEN => 1], $collection->toArray());
    }

    private function createModifierConfig(string $range, string $action, string $criteria, ?string $eventToRemove, ?string $eventToAdd, ?int $weight): ExplorationEventModifierConfig
    {
        $dto = new ExplorationEventModifierConfigDto(
            key: 'test',
            name: 'test',
            strategy: ModifierStrategyEnum::EXPLORATION_SECTOR_SELECTION_MODIFIER,
            modifierRange: $range,
            modifierActivationRequirements: [],
            targetEvent: ExplorationSelectionEvent::SECTOR_SELECTION,
            priority: ModifierPriorityEnum::EXPLORATION_DIPLOMAT,
            tagConstraints: [],
            action: $action,
            criteria: $criteria,
            eventToRemove: $eventToRemove,
            eventToAdd: $eventToAdd,
            weight: $weight
        );

        return ExplorationEventModifierConfig::fromDtoChild($dto);
    }

    private function createModifier(ModifierHolderInterface $holder, ModifierProviderInterface $provider, ExplorationEventModifierConfig $config): void
    {
        $this->modifierCreationService->createModifier(
            $config,
            $holder,
            $provider
        );
    }
}
