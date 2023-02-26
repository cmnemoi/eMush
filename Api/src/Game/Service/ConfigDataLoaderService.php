<?php

namespace Mush\Game\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Daedalus\ConfigData\DaedalusConfigDataLoader;
use Mush\Daedalus\ConfigData\RandomItemPlacesDataLoader;
use Mush\Disease\ConfigData\ConsumableDiseaseConfigDataLoader;
use Mush\Disease\ConfigData\DiseaseCauseConfigDataLoader;
use Mush\Disease\ConfigData\DiseaseConfigDataLoader;
use Mush\Disease\ConfigData\SymptomActivationRequirementDataLoader;
use Mush\Disease\ConfigData\SymptomConfigDataLoader;
use Mush\Equipment\ConfigData\BlueprintDataLoader;
use Mush\Equipment\ConfigData\BookDataLoader;
use Mush\Equipment\ConfigData\DocumentDataLoader;
use Mush\Equipment\ConfigData\DrugDataLoader;
use Mush\Equipment\ConfigData\EquipmentConfigDataLoader;
use Mush\Equipment\ConfigData\FruitDataLoader;
use Mush\Equipment\ConfigData\GearDataLoader;
use Mush\Equipment\ConfigData\ItemConfigDataLoader;
use Mush\Equipment\ConfigData\PlantDataLoader;
use Mush\Equipment\ConfigData\RationDataLoader;
use Mush\Equipment\ConfigData\ToolDataLoader;
use Mush\Equipment\ConfigData\WeaponDataLoader;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\ConfigData\DifficultyConfigDataLoader;
use Mush\Game\ConfigData\GameConfigDataLoader;
use Mush\Game\ConfigData\LocalizationConfigDataLoader;
use Mush\Game\ConfigData\TriumphConfigDataLoader;
use Mush\Game\ConfigData\VariableEventConfigDataLoader;
use Mush\Modifier\ConfigData\DirectModifierConfigDataLoader;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\PreventEventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Place\ConfigData\PlaceConfigDataLoader;
use Mush\Player\ConfigData\CharacterConfigDataLoader;
use Mush\Status\ConfigData\ChargeStatusConfigDataLoader;
use Mush\Status\ConfigData\StatusConfigDataLoader;

class ConfigDataLoaderService
{
    private ArrayCollection $dataLoaders;

    public function __construct(ModifierActivationRequirementDataLoader $modifierActivationRequirementDataLoader,
                                VariableEventModifierConfigDataLoader $variableEventModifierConfigDataLoader,
                                TriggerEventModifierConfigDataLoader $triggerEventModifierConfigDataLoader,
                                DirectModifierConfigDataLoader $directModifierConfigDataLoader,
                                VariableEventConfigDataLoader $variableEventConfigDataLoader,
                                PreventEventModifierConfigDataLoader $preventEventModifierConfigDataLoader,
                                ChargeStatusConfigDataLoader $chargeStatusConfigDataLoader,
                                StatusConfigDataLoader $statusConfigDataLoader,
                                SymptomActivationRequirementDataLoader $symptomActivationRequirementDataLoader,
                                SymptomConfigDataLoader $symptomConfigDataLoader,
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
                                ItemConfigDataLoader $itemConfigDataLoader,
                                EquipmentConfigDataLoader $equipmentConfigDataLoader,
                                CharacterConfigDataLoader $characterConfigDataLoader,
                                RandomItemPlacesDataLoader $randomItemPlacesDataLoader,
                                PlaceConfigDataLoader $placeConfigDataLoader,
                                DaedalusConfigDataLoader $daedalusConfigDataLoader,
                                DifficultyConfigDataLoader $difficultyConfigDataLoader,
                                TriumphConfigDataLoader $triumphConfigDataLoader,
                                DiseaseCauseConfigDataLoader $diseaseCauseConfigDataLoader,
                                ConsumableDiseaseConfigDataLoader $consumableDiseaseConfigDataLoader,
                                GameConfigDataLoader $gameConfigDataLoader,
                                LocalizationConfigDataLoader $localizationConfigDataLoader
    ) {
        // add data loaders in order of dependencies
        /** @var ArrayCollection<int, ConfigDataLoader> $dataLoaders */
        $dataLoaders = new ArrayCollection(
            [
                $variableEventConfigDataLoader,
                $modifierActivationRequirementDataLoader,
                $variableEventModifierConfigDataLoader,
                $triggerEventModifierConfigDataLoader,
                $preventEventModifierConfigDataLoader,
                $directModifierConfigDataLoader,
                $chargeStatusConfigDataLoader,
                $statusConfigDataLoader,
                $symptomActivationRequirementDataLoader,
                $symptomConfigDataLoader,
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
                $weaponDataLoader,
                $equipmentConfigDataLoader,
                $itemConfigDataLoader,
                $characterConfigDataLoader,
                $randomItemPlacesDataLoader,
                $placeConfigDataLoader,
                $daedalusConfigDataLoader,
                $difficultyConfigDataLoader,
                $triumphConfigDataLoader,
                $diseaseCauseConfigDataLoader,
                $consumableDiseaseConfigDataLoader,
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

    /** @psalm-param ArrayCollection<int, ConfigDataLoader> $dataLoaders **/
    private function setDataLoaders(ArrayCollection $dataLoaders): void
    {
        $this->dataLoaders = $dataLoaders;
    }
}
