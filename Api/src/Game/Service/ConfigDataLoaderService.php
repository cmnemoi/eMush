<?php

declare(strict_types=1);

namespace Mush\Game\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Communications\ConfigData\RebelBaseConfigDataLoader;
use Mush\Communications\ConfigData\TradeAssetConfigDataLoader;
use Mush\Communications\ConfigData\TradeConfigDataLoader;
use Mush\Communications\ConfigData\TradeOptionConfigDataLoader;
use Mush\Communications\ConfigData\XylophConfigDataLoader;
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
use Mush\Equipment\ConfigData\PlumbingDataLoader;
use Mush\Equipment\ConfigData\RationDataLoader;
use Mush\Equipment\ConfigData\ToolDataLoader;
use Mush\Equipment\ConfigData\WeaponDataLoader;
use Mush\Equipment\ConfigData\WeaponEffectConfigDataLoader;
use Mush\Equipment\ConfigData\WeaponEventConfigDataLoader;
use Mush\Exploration\ConfigData\PlanetSectorConfigDataLoader;
use Mush\Exploration\ConfigData\PlanetSectorEventConfigDataLoader;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\ConfigData\DifficultyConfigDataLoader;
use Mush\Game\ConfigData\GameConfigDataLoader;
use Mush\Game\ConfigData\LocalizationConfigDataLoader;
use Mush\Game\ConfigData\TitleConfigDataLoader;
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
use Mush\Triumph\ConfigData\TriumphConfigDataLoader;

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
        PlumbingDataLoader $plumbingDataLoader,
        RationDataLoader $rationDataLoader,
        ToolDataLoader $toolDataLoader,
        WeaponEventConfigDataLoader $weaponEventConfigDataLoader,
        WeaponEffectConfigDataLoader $weaponEffectConfigDataLoader,
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
        RebelBaseConfigDataLoader $rebelBaseConfigDataLoader,
        XylophConfigDataLoader $xylophConfigDataLoader,
        TradeAssetConfigDataLoader $tradeAssetConfigDataLoader,
        TradeOptionConfigDataLoader $tradeOptionConfigDataLoader,
        TradeConfigDataLoader $tradeConfigDataLoader,
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
                $plumbingDataLoader,
                $rationDataLoader,
                $toolDataLoader,
                $weaponEventConfigDataLoader,
                $weaponEffectConfigDataLoader,
                $weaponDataLoader,
                $equipmentConfigDataLoader,
                $patrolShipDataLoader,
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
                $rebelBaseConfigDataLoader,
                $xylophConfigDataLoader,
                $tradeAssetConfigDataLoader,
                $tradeOptionConfigDataLoader,
                $tradeConfigDataLoader,
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
