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
use Mush\Equipment\ConfigData\ContainerDataLoader;
use Mush\Equipment\ConfigData\DocumentDataLoader;
use Mush\Equipment\ConfigData\DroneDataLoader;
use Mush\Equipment\ConfigData\DrugDataLoader;
use Mush\Equipment\ConfigData\EquipmentCommandConfigDataLoader;
use Mush\Equipment\ConfigData\EquipmentConfigDataLoader;
use Mush\Equipment\ConfigData\FruitDataLoader;
use Mush\Equipment\ConfigData\GearDataLoader;
use Mush\Equipment\ConfigData\ItemConfigDataLoader;
use Mush\Equipment\ConfigData\PatrolShipDataLoader;
use Mush\Equipment\ConfigData\PlantDataLoader;
use Mush\Equipment\ConfigData\RationDataLoader;
use Mush\Equipment\ConfigData\ToolDataLoader;
use Mush\Equipment\ConfigData\WeaponDataLoader;
use Mush\Equipment\ConfigData\WeaponEffect\BreakWeaponEffectConfigDataLoader;
use Mush\Equipment\ConfigData\WeaponEffect\DropWeaponEffectConfigDataLoader;
use Mush\Equipment\ConfigData\WeaponEffect\InflictInjuryWeaponEffectConfigDataLoader;
use Mush\Equipment\ConfigData\WeaponEffect\InflictRandomInjuryWeaponEffectConfigDataLoader;
use Mush\Equipment\ConfigData\WeaponEffect\ModifyDamageWeaponEffectConfigDataLoader;
use Mush\Equipment\ConfigData\WeaponEffect\ModifyMaxDamageWeaponEffectConfigDataLoader;
use Mush\Equipment\ConfigData\WeaponEffect\OneShotWeaponEffectConfigDataLoader;
use Mush\Equipment\ConfigData\WeaponEffect\RemoveActionPointsWeaponEffectConfigDataLoader;
use Mush\Equipment\ConfigData\WeaponEventConfigDataLoader;
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
use Mush\Project\ConfigData\ProjectRequirementsDataLoader;
use Mush\Skill\ConfigData\SkillConfigDataLoader;
use Mush\Status\ConfigData\ChargeStatusConfigDataLoader;
use Mush\Status\ConfigData\ContentStatusConfigDataLoader;
use Mush\Status\ConfigData\StatusConfigDataLoader;

/**
 * @SuppressWarnings(PHPMD)
 */
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
        ActionDataLoader $actionDataLoader,
        ChargeStatusConfigDataLoader $chargeStatusConfigDataLoader,
        ContentStatusConfigDataLoader $contentStatusConfigDataLoader,
        StatusConfigDataLoader $statusConfigDataLoader,
        DiseaseConfigDataLoader $diseaseConfigDataLoader,
        BookDataLoader $bookDataLoader,
        BlueprintDataLoader $blueprintDataLoader,
        ContainerDataLoader $containerDataLoader,
        DocumentDataLoader $documentDataLoader,
        DrugDataLoader $drugDataLoader,
        FruitDataLoader $fruitDataLoader,
        GearDataLoader $gearDataLoader,
        PlantDataLoader $plantDataLoader,
        RationDataLoader $rationDataLoader,
        ToolDataLoader $toolDataLoader,
        WeaponEventConfigDataLoader $weaponEventConfigDataLoader,
        BreakWeaponEffectConfigDataLoader $breakWeaponEffectConfigDataLoader,
        DropWeaponEffectConfigDataLoader $dropWeaponEffectConfigDataLoader,
        OneShotWeaponEffectConfigDataLoader $oneShotWeaponEffectConfigDataLoader,
        ModifyDamageWeaponEffectConfigDataLoader $modifyDamageWeaponEffectConfigDataLoader,
        ModifyMaxDamageWeaponEffectConfigDataLoader $modifyMaxDamageWeaponEffectConfigDataLoader,
        RemoveActionPointsWeaponEffectConfigDataLoader $removeActionPointsWeaponEffectConfigDataLoader,
        InflictInjuryWeaponEffectConfigDataLoader $inflictInjuryWeaponEffectConfigDataLoader,
        InflictRandomInjuryWeaponEffectConfigDataLoader $inflictRandomInjuryWeaponEffectConfigDataLoader,
        WeaponDataLoader $weaponDataLoader,
        PatrolShipDataLoader $patrolShipDataLoader,
        ItemConfigDataLoader $itemConfigDataLoader,
        DroneDataLoader $droneDataLoader,
        EquipmentConfigDataLoader $equipmentConfigDataLoader,
        EquipmentCommandConfigDataLoader $equipmentCommandConfigDataLoader,
        SkillConfigDataLoader $skillConfigDataLoader,
        CharacterConfigDataLoader $characterConfigDataLoader,
        RandomItemPlacesDataLoader $randomItemPlacesDataLoader,
        PlaceConfigDataLoader $placeConfigDataLoader,
        DaedalusConfigDataLoader $daedalusConfigDataLoader,
        DifficultyConfigDataLoader $difficultyConfigDataLoader,
        TitleConfigDataLoader $titleConfigDataLoader,
        TriumphConfigDataLoader $triumphConfigDataLoader,
        DiseaseCauseConfigDataLoader $diseaseCauseConfigDataLoader,
        ConsumableDiseaseConfigDataLoader $consumableDiseaseConfigDataLoader,
        HunterConfigDataLoader $hunterConfigDataLoader,
        PlanetSectorConfigDataLoader $planetSectorConfigDataLoader,
        ProjectRequirementsDataLoader $projectRequirementsDataLoader,
        ProjectConfigDataLoader $projectConfigDataLoader,
        GameConfigDataLoader $gameConfigDataLoader,
        LocalizationConfigDataLoader $localizationConfigDataLoader,
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
                $actionDataLoader,
                $chargeStatusConfigDataLoader,
                $contentStatusConfigDataLoader,
                $statusConfigDataLoader,
                $diseaseConfigDataLoader,
                $blueprintDataLoader,
                $bookDataLoader,
                $containerDataLoader,
                $documentDataLoader,
                $drugDataLoader,
                $fruitDataLoader,
                $gearDataLoader,
                $plantDataLoader,
                $rationDataLoader,
                $toolDataLoader,
                $patrolShipDataLoader,
                $weaponEventConfigDataLoader,
                $breakWeaponEffectConfigDataLoader,
                $dropWeaponEffectConfigDataLoader,
                $oneShotWeaponEffectConfigDataLoader,
                $modifyDamageWeaponEffectConfigDataLoader,
                $modifyMaxDamageWeaponEffectConfigDataLoader,
                $removeActionPointsWeaponEffectConfigDataLoader,
                $inflictInjuryWeaponEffectConfigDataLoader,
                $inflictRandomInjuryWeaponEffectConfigDataLoader,
                $weaponDataLoader,
                $equipmentConfigDataLoader,
                $itemConfigDataLoader,
                $droneDataLoader,
                $equipmentCommandConfigDataLoader,
                $skillConfigDataLoader,
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
                $projectRequirementsDataLoader,
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
