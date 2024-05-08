<?php

namespace Mush\Game\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Daedalus\ConfigData\DaedalusConfigDataLoader;
use Mush\Daedalus\ConfigData\RandomItemPlacesDataLoader;
use Mush\Disease\ConfigData\ConsumableDiseaseConfigDataLoader;
use Mush\Disease\ConfigData\DiseaseCauseConfigDataLoader;
use Mush\Disease\ConfigData\DiseaseConfigDataLoader;
use Mush\Equipment\ConfigData\BlueprintDataLoader;
use Mush\Equipment\ConfigData\BookDataLoader;
use Mush\Equipment\ConfigData\DocumentDataLoader;
use Mush\Equipment\ConfigData\DrugDataLoader;
use Mush\Equipment\ConfigData\EquipmentConfigDataLoader;
use Mush\Equipment\ConfigData\FruitDataLoader;
use Mush\Equipment\ConfigData\GearDataLoader;
use Mush\Equipment\ConfigData\ItemConfigDataLoader;
use Mush\Equipment\ConfigData\PatrolShipDataLoader;
use Mush\Equipment\ConfigData\PlantDataLoader;
use Mush\Equipment\ConfigData\RationDataLoader;
use Mush\Equipment\ConfigData\EquipmentCommandConfigDataLoader;
use Mush\Equipment\ConfigData\ToolDataLoader;
use Mush\Equipment\ConfigData\WeaponDataLoader;
use Mush\Exploration\ConfigData\PlanetSectorConfigDataLoader;
use Mush\Exploration\ConfigData\PlanetSectorEventConfigDataLoader;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\ConfigData\DifficultyConfigDataLoader;
use Mush\Game\ConfigData\GameConfigDataLoader;
use Mush\Game\ConfigData\LocalizationConfigDataLoader;
use Mush\Game\ConfigData\TitleConfigDataLoader;
use Mush\Game\ConfigData\TriumphConfigDataLoader;
use Mush\Game\ConfigData\VariableEventConfigDataLoader;
use Mush\Hunter\ConfigData\HunterConfigDataLoader;
use Mush\Modifier\ConfigData\DirectModifierConfigDataLoader;
use Mush\Modifier\ConfigData\EventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Place\ConfigData\PlaceConfigDataLoader;
use Mush\Player\ConfigData\CharacterConfigDataLoader;
use Mush\Project\ConfigData\ProjectConfigDataLoader;
use Mush\Status\ConfigData\ChargeStatusConfigDataLoader;
use Mush\Status\ConfigData\ContentStatusConfigDataLoader;
use Mush\Status\ConfigData\StatusConfigDataLoader;

class ConfigDataLoaderService
{
    private ArrayCollection $dataLoaders;

    public function __construct(
        ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader,
        EventModifierConfigDataLoader $eventModifierConfigDataLoader,
        VariableEventModifierConfigDataLoader $variableEventModifierConfigDataLoader,
        TriggerEventModifierConfigDataLoader $triggerEventModifierConfigDataLoader,
        DirectModifierConfigDataLoader $directModifierConfigDataLoader,
        VariableEventConfigDataLoader $variableEventConfigDataLoader,
        PlanetSectorEventConfigDataLoader $planetSectorEventConfigDataLoader,
        ChargeStatusConfigDataLoader $chargeStatusConfigDataLoader,
        ContentStatusConfigDataLoader $contentStatusConfigDataLoader,
        StatusConfigDataLoader $statusConfigDataLoader,
        DiseaseConfigDataLoader $diseaseConfigDataLoader,
        ActionDataLoader $actionDataLoader,
        BookDataLoader $bookDataLoader,
        BlueprintDataLoader $blueprintDataLoader,
        DocumentDataLoader $documentDataLoader,
        DrugDataLoader $drugDataLoader,
        FruitDataLoader $fruitDataLoader,
        GearDataLoader $gearDataLoader,
        PlantDataLoader $plantDataLoader,
        RationDataLoader $rationDataLoader,
        ToolDataLoader $toolDataLoader,
        WeaponDataLoader $weaponDataLoader,
        PatrolShipDataLoader $patrolShipDataLoader,
        ItemConfigDataLoader $itemConfigDataLoader,
        EquipmentConfigDataLoader $equipmentConfigDataLoader,
        CharacterConfigDataLoader $characterConfigDataLoader,
        RandomItemPlacesDataLoader        $randomItemPlacesDataLoader,
        PlaceConfigDataLoader             $placeConfigDataLoader,
        DaedalusConfigDataLoader          $daedalusConfigDataLoader,
        DifficultyConfigDataLoader        $difficultyConfigDataLoader,
        TitleConfigDataLoader             $titleConfigDataLoader,
        TriumphConfigDataLoader           $triumphConfigDataLoader,
        DiseaseCauseConfigDataLoader      $diseaseCauseConfigDataLoader,
        ConsumableDiseaseConfigDataLoader $consumableDiseaseConfigDataLoader,
        HunterConfigDataLoader            $hunterConfigDataLoader,
        PlanetSectorConfigDataLoader      $planetSectorConfigDataLoader,
        EquipmentCommandConfigDataLoader  $equipmentCommandConfigDataLoader,
        ProjectConfigDataLoader           $projectConfigDataLoader,
        GameConfigDataLoader              $gameConfigDataLoader,
        LocalizationConfigDataLoader      $localizationConfigDataLoader,
    ) {
        // add data loaders in order of dependencies
        /** @var ArrayCollection<int, ConfigDataLoader> $dataLoaders */
        $dataLoaders = new ArrayCollection(
            [
                $variableEventConfigDataLoader,
                $modifierActivationRequirementDataLoader,
                $eventModifierConfigDataLoader,
                $variableEventModifierConfigDataLoader,
                $planetSectorEventConfigDataLoader,
                $triggerEventModifierConfigDataLoader,
                $directModifierConfigDataLoader,
                $chargeStatusConfigDataLoader,
                $contentStatusConfigDataLoader,
                $statusConfigDataLoader,
                $diseaseConfigDataLoader,
                $actionDataLoader,
                $blueprintDataLoader,
                $bookDataLoader,
                $documentDataLoader,
                $drugDataLoader,
                $fruitDataLoader,
                $gearDataLoader,
                $plantDataLoader,
                $rationDataLoader,
                $toolDataLoader,
                $patrolShipDataLoader,
                $weaponDataLoader,
                $equipmentConfigDataLoader,
                $itemConfigDataLoader,
                $characterConfigDataLoader,
                $randomItemPlacesDataLoader,
                $placeConfigDataLoader,
                $daedalusConfigDataLoader,
                $difficultyConfigDataLoader,
                $titleConfigDataLoader,
                $triumphConfigDataLoader,
                $diseaseCauseConfigDataLoader,
                $consumableDiseaseConfigDataLoader,
                $hunterConfigDataLoader,
                $planetSectorConfigDataLoader,
                $equipmentCommandConfigDataLoader,
                $projectConfigDataLoader,
                $gameConfigDataLoader,
                $localizationConfigDataLoader,
            ]
        );
        $this->setDataLoaders($dataLoaders);
    }

    public function loadAllConfigsData(): void
    {
        /** @var ConfigDataLoader $dataLoader */
        foreach ($this->dataLoaders as $dataLoader) {
            $dataLoader->loadConfigsData();
        }
    }

    /** @psalm-param ArrayCollection<int, ConfigDataLoader> $dataLoaders */
    private function setDataLoaders(ArrayCollection $dataLoaders): void
    {
        $this->dataLoaders = $dataLoaders;
    }
}
