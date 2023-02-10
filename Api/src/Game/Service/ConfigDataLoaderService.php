<?php

namespace Mush\Game\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\ConfigData\ActionDataLoader;
use Mush\Action\Repository\ActionRepository;
use Mush\Daedalus\ConfigData\DaedalusConfigDataLoader;
use Mush\Daedalus\ConfigData\RandomItemPlacesDataLoader;
use Mush\Daedalus\Repository\DaedalusConfigRepository;
use Mush\Daedalus\Repository\RandomItemPlacesRepository;
use Mush\Disease\ConfigData\ConsumableDiseaseConfigDataLoader;
use Mush\Disease\ConfigData\DiseaseCauseConfigDataLoader;
use Mush\Disease\ConfigData\DiseaseConfigDataLoader;
use Mush\Disease\ConfigData\SymptomActivationRequirementDataLoader;
use Mush\Disease\ConfigData\SymptomConfigDataLoader;
use Mush\Disease\Repository\ConsumableDiseaseConfigRepository;
use Mush\Disease\Repository\DiseaseCauseConfigRepository;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Disease\Repository\SymptomActivationRequirementRepository;
use Mush\Disease\Repository\SymptomConfigRepository;
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
use Mush\Equipment\Repository\EquipmentConfigRepository;
use Mush\Equipment\Repository\MechanicsRepository;
use Mush\Game\ConfigData\ConfigDataLoader;
use Mush\Game\ConfigData\DifficultyConfigDataLoader;
use Mush\Game\ConfigData\GameConfigDataLoader;
use Mush\Game\ConfigData\TriumphConfigDataLoader;
use Mush\Game\Repository\DifficultyConfigRepository;
use Mush\Game\Repository\GameConfigRepository;
use Mush\Game\Repository\TriumphConfigRepository;
use Mush\Modifier\ConfigData\ModifierActivationRequirementDataLoader;
use Mush\Modifier\ConfigData\TriggerEventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\TriggerVariableEventModifierConfigDataLoader;
use Mush\Modifier\ConfigData\VariableEventModifierConfigDataLoader;
use Mush\Modifier\Repository\ModifierActivationRequirementRepository;
use Mush\Modifier\Repository\ModifierConfigRepository;
use Mush\Place\ConfigData\PlaceConfigDataLoader;
use Mush\Place\Repository\PlaceConfigRepository;
use Mush\Player\ConfigData\CharacterConfigDataLoader;
use Mush\Player\Repository\CharacterConfigRepository;
use Mush\Status\ConfigData\StatusConfigDataLoader;
use Mush\Status\Repository\StatusConfigRepository;

class ConfigDataLoaderService
{
    private EntityManagerInterface $entityManager;
    private ModifierActivationRequirementRepository $modifierActivationRequirementRepository;
    private ModifierConfigRepository $modifierConfigRepository;
    private StatusConfigRepository $statusConfigRepository;
    private SymptomActivationRequirementRepository $symptomActivationRequirement;
    private SymptomConfigRepository $symptomConfigRepository; // TODO: remove when deprecated
    private DiseaseConfigRepository $diseaseConfigRepository;
    private ActionRepository $actionRepository;
    private MechanicsRepository $mechanicsRepository;
    private EquipmentConfigRepository $equipmentConfigRepository;
    private CharacterConfigRepository $characterConfigRepository;
    private RandomItemPlacesRepository $randomItemPlacesRepository;
    private PlaceConfigRepository $placeConfigRepository;
    private DaedalusConfigRepository $daedalusConfigRepository;
    private DifficultyConfigRepository $difficultyConfigRepository;
    private TriumphConfigRepository $triumphConfigRepository;
    private DiseaseCauseConfigRepository $diseaseCauseConfigRepository;
    private ConsumableDiseaseConfigRepository $consumableDiseaseConfigRepository;
    private GameConfigRepository $gameConfigRepository;

    private ArrayCollection $dataLoaders;

    public function __construct(EntityManagerInterface $entityManager,
                                ModifierActivationRequirementRepository $modifierActivationRequirementRepository,
                                ModifierConfigRepository $modifierConfigRepository,
                                StatusConfigRepository $statusConfigRepository,
                                SymptomActivationRequirementRepository $symptomActivationRequirement,
                                SymptomConfigRepository $symptomConfigRepository,
                                DiseaseConfigRepository $diseaseConfigRepository,
                                ActionRepository $actionRepository,
                                MechanicsRepository $mechanicsRepository,
                                EquipmentConfigRepository $equipmentConfigRepository,
                                CharacterConfigRepository $characterConfigRepository,
                                RandomItemPlacesRepository $randomItemPlacesRepository,
                                PlaceConfigRepository $placeConfigRepository,
                                DaedalusConfigRepository $daedalusConfigRepository,
                                DifficultyConfigRepository $difficultyConfigRepository,
                                TriumphConfigRepository $triumphConfigRepository,
                                DiseaseCauseConfigRepository $diseaseCauseConfigRepository,
                                ConsumableDiseaseConfigRepository $consumableDiseaseConfigRepository,
                                GameConfigRepository $gameConfigRepository
    ) {
        
        /** @var ConfigDataLoader $modifierActivationRequirement */
        $modifierActivationRequirementDataLoader = new ModifierActivationRequirementDataLoader(
            $entityManager,
            $modifierActivationRequirementRepository
        );
        /** @var ConfigDataLoader $variableEventModifierConfigDataLoader */
        $variableEventModifierConfigDataLoader = new VariableEventModifierConfigDataLoader(
            $entityManager,
            $modifierConfigRepository,
            $modifierActivationRequirementRepository
        );
        /** @var ConfigDataLoader $triggerEventModifierConfigDataLoader */
        $triggerEventModifierConfigDataLoader = new TriggerEventModifierConfigDataLoader(
            $entityManager,
            $modifierConfigRepository,
            $modifierActivationRequirementRepository
        );
        /** @var ConfigDataLoader $triggerVariableEventModifierConfigDataLoader */
        $triggerVariableEventModifierConfigDataLoader = new TriggerVariableEventModifierConfigDataLoader(
            $entityManager,
            $modifierConfigRepository,
            $modifierActivationRequirementRepository
        );
        /** @var ConfigDataLoader $statusConfigDataLoader */
        $statusConfigDataLoader = new StatusConfigDataLoader(
            $entityManager,
            $statusConfigRepository,
            $modifierConfigRepository,
        );
        /** @var ConfigDataLoader $symptomActivationRequirementDataLoader */
        $symptomActivationRequirementDataLoader = new SymptomActivationRequirementDataLoader(
            $entityManager,
            $symptomActivationRequirement,
        );
        /** @var ConfigDataLoader $symptomConfigDataLoader */
        $symptomConfigDataLoader = new SymptomConfigDataLoader(
            $entityManager,
            $symptomConfigRepository,
            $symptomActivationRequirement,
        );
        /** @var ConfigDataLoader $diseaseConfigDataLoader */
        $diseaseConfigDataLoader = new DiseaseConfigDataLoader(
            $entityManager,
            $diseaseConfigRepository,
            $modifierConfigRepository,
            $symptomConfigRepository,
        );
        /** @var ConfigDataLoader $actionDataLoader */
        $actionDataLoader = new ActionDataLoader($entityManager, $actionRepository);
        /** @var ConfigDataLoader $blueprintDataLoader */
        $blueprintDataLoader = new BlueprintDataLoader(
            $entityManager, 
            $mechanicsRepository,
            $actionRepository
        );
        /** @var ConfigDataLoader $bookDataLoader */
        $bookDataLoader = new BookDataLoader(
            $entityManager, 
            $mechanicsRepository, 
            $actionRepository
        );
        /** @var ConfigDataLoader $documentDataLoader */
        $documentDataLoader = new DocumentDataLoader(
            $entityManager, 
            $mechanicsRepository, 
            $actionRepository
        );
        /** @var ConfigDataLoader $drugDataLoader */
        $drugDataLoader = new DrugDataLoader(
            $entityManager, 
            $mechanicsRepository, 
            $actionRepository
        );
        /** @var ConfigDataLoader $fruitDataLoader */
        $fruitDataLoader = new FruitDataLoader(
            $entityManager, 
            $mechanicsRepository, 
            $actionRepository
        );
        /** @var ConfigDataLoader $gearDataLoader */
        $gearDataLoader = new GearDataLoader(
            $entityManager, 
            $mechanicsRepository, 
            $actionRepository,
            $modifierConfigRepository
        );
        /** @var ConfigDataLoader $plantDataLoader */
        $plantDataLoader = new PlantDataLoader(
            $entityManager, 
            $mechanicsRepository, 
            $actionRepository
        );
        /** @var ConfigDataLoader $rationDataLoader */
        $rationDataLoader = new RationDataLoader(
            $entityManager, 
            $mechanicsRepository, 
            $actionRepository
        );
        /** @var ConfigDataLoader $toolDataLoader */
        $toolDataLoader = new ToolDataLoader(
            $entityManager, 
            $mechanicsRepository, 
            $actionRepository
        );
        /** @var ConfigDataLoader $weaponDataLoader */
        $weaponDataLoader = new WeaponDataLoader(
            $entityManager, 
            $mechanicsRepository, 
            $actionRepository
        );
        /** @var ConfigDataLoader $equipmentConfigDataLoader */
        $equipmentConfigDataLoader = new EquipmentConfigDataLoader(
            $entityManager,
            $equipmentConfigRepository,
            $actionRepository,
            $mechanicsRepository,
            $statusConfigRepository
        );
        /** @var ConfigDataLoader $itemConfigDataLoader */
        $itemConfigDataLoader = new ItemConfigDataLoader(
            $entityManager,
            $equipmentConfigRepository,
            $actionRepository,
            $mechanicsRepository,
            $statusConfigRepository
        );
        /** @var ConfigDataLoader $characterConfigDataLoader */
        $characterConfigDataLoader = new CharacterConfigDataLoader(
            $entityManager,
            $characterConfigRepository,
            $actionRepository,
            $diseaseConfigRepository,
            $equipmentConfigRepository,
            $statusConfigRepository
        );
        /** @var ConfigDataLoader $randomItemPlacesDataLoader */
        $randomItemPlacesDataLoader = new RandomItemPlacesDataLoader(
            $entityManager,
            $randomItemPlacesRepository,
        );
        /** @var ConfigDataLoader $placeConfigDataLoader */
        $placeConfigDataLoader = new PlaceConfigDataLoader(
            $entityManager,
            $placeConfigRepository,
        );
        /** @var ConfigDataLoader $daedalusConfigDataLoader */
        $daedalusConfigDataLoader = new DaedalusConfigDataLoader(
            $entityManager,
            $daedalusConfigRepository,
            $placeConfigRepository,
            $randomItemPlacesRepository
        );
        /** @var ConfigDataLoader $difficultyConfigDataLoader */
        $difficultyConfigDataLoader = new DifficultyConfigDataLoader(
            $entityManager,
            $difficultyConfigRepository,
        );
        /** @var ConfigDataLoader $triumphConfigDataLoader */
        $triumphConfigDataLoader = new TriumphConfigDataLoader(
            $entityManager,
            $triumphConfigRepository,
        );
        /** @var ConfigDataLoader $diseaseCauseConfigDataLoader */
        $diseaseCauseConfigDataLoader = new DiseaseCauseConfigDataLoader(
            $entityManager,
            $diseaseCauseConfigRepository,
        );
        /** @var ConfigDataLoader $consumableDiseaseConfigDataLoader */
        $consumableDiseaseConfigDataLoader = new ConsumableDiseaseConfigDataLoader(
            $entityManager,
            $consumableDiseaseConfigRepository,
        );
        /** @var ConfigDataLoader $gameConfigDataLoader */
        $gameConfigDataLoader = new GameConfigDataLoader(
            $entityManager,
            $gameConfigRepository,
            $daedalusConfigRepository,
            $difficultyConfigRepository,
            $characterConfigRepository,
            $statusConfigRepository,
            $equipmentConfigRepository,
            $triumphConfigRepository,
            $diseaseCauseConfigRepository,
            $diseaseConfigRepository,
            $consumableDiseaseConfigRepository
        );

        /** @var ArrayCollection<int, ConfigDataLoader> $dataLoaders */
        // add data loaders in order of dependencies
        $dataLoaders = new ArrayCollection(
            [
                $modifierActivationRequirementDataLoader,
                $variableEventModifierConfigDataLoader,
                $triggerEventModifierConfigDataLoader,
                $triggerVariableEventModifierConfigDataLoader,
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
                $gameConfigDataLoader
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
