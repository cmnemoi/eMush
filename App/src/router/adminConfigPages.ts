import GameConfigListPage from "@/components/Admin/Config/GameConfig/GameConfigListPage.vue";
import GameConfigDetailPage from "@/components/Admin/Config/GameConfig/GameConfigDetailPage.vue";
import ModifierConfigListPage from "@/components/Admin/Config/ModifierConfig/ModifierConfigListPage.vue";
import ModifierConfigDetailPage from "@/components/Admin/Config/ModifierConfig/ModifierConfigDetailPage.vue";
import ModifierActivationRequirementListPage from "@/components/Admin/Config/ModifierActivationRequirement/ModifierActivationRequirementListPage.vue";
import ModifierActivationRequirementDetailPage from "@/components/Admin/Config/ModifierActivationRequirement/ModifierActivationRequirementDetailPage.vue";
import StatusConfigListPage from "@/components/Admin/Config/StatusConfig/StatusConfigListPage.vue";
import StatusConfigDetailPage from "@/components/Admin/Config/StatusConfig/StatusConfigDetailPage.vue";
import ActionCostListPage from "@/components/Admin/Config/ActionCost/ActionCostListPage.vue";
import ActionCostDetailPage from "@/components/Admin/Config/ActionCost/ActionCostDetailPage.vue";
import ActionConfigListPage from "@/components/Admin/Config/ActionConfig/ActionConfigListPage.vue";
import ActionConfigDetailPage from "@/components/Admin/Config/ActionConfig/ActionConfigDetailPage.vue";
import DaedalusConfigListPage from "@/components/Admin/Config/DaedalusConfig/DaedalusConfigListPage.vue";
import DaedalusConfigDetailPage from "@/components/Admin/Config/DaedalusConfig/DaedalusConfigDetailPage.vue";
import DifficultyConfigListPage from "@/components/Admin/Config/DifficultyConfig/DifficultyConfigListPage.vue";
import DifficultyConfigDetailPage from "@/components/Admin/Config/DifficultyConfig/DifficultyConfigDetailPage.vue";
import CharacterConfigListPage from "@/components/Admin/Config/CharacterConfig/CharacterConfigListPage.vue";
import CharacterConfigDetailPage from "@/components/Admin/Config/CharacterConfig/CharacterConfigDetailPage.vue";
import DiseaseConfigListPage from "@/components/Admin/Config/DiseaseConfig/DiseaseConfigListPage.vue";
import DiseaseConfigDetailPage from "@/components/Admin/Config/DiseaseConfig/DiseaseConfigDetailPage.vue";
import SymptomConfigListPage from "@/components/Admin/Config/SymptomConfig/SymptomConfigListPage.vue";
import SymptomConfigDetailPage from "@/components/Admin/Config/SymptomConfig/SymptomConfigDetailPage.vue";
import SymptomActivationRequirementListPage from "@/components/Admin/Config/SymptomActivationRequirement/SymptomActivationRequirementListPage.vue";
import SymptomActivationRequirementDetailPage from "@/components/Admin/Config/SymptomActivationRequirement/SymptomActivationRequirementDetailPage.vue";
import EquipmentConfigListPage from "@/components/Admin/Config/EquipmentConfig/EquipmentConfigListPage.vue";
import EquipmentConfigDetailPage from "@/components/Admin/Config/EquipmentConfig/EquipmentConfigDetailPage.vue";
import PlaceConfigDetailPage from "@/components/Admin/Config/PlaceConfig/PlaceConfigDetailPage.vue";
import PlaceConfigListPage from "@/components/Admin/Config/PlaceConfig/PlaceConfigListPage.vue";
import RandomItemPlacesListPage from "@/components/Admin/Config/RandomItemPlaces/RandomItemPlacesListPage.vue";
import RandomItemPlacesDetailPage from "@/components/Admin/Config/RandomItemPlaces/RandomItemPlacesDetailPage.vue";
import MechanicsListPage from "@/components/Admin/Config/Mechanics/MechanicsListPage.vue";
import MechanicsDetailPage from "@/components/Admin/Config/Mechanics/MechanicsDetailPage.vue";

export const adminConfigRoutes = [
    {
        name: "AdminGameConfigList",
        path: 'game-config-list',
        component: GameConfigListPage
    },
    {
        name: "AdminGameConfigDetail",
        path: 'game-config/:gameConfigId',
        component: GameConfigDetailPage
    },
    {
        name: "AdminModifierConfigList",
        path: 'modifier-config-list',
        component: ModifierConfigListPage
    },
    {
        name: "AdminModifierConfigDetail",
        path: 'modifier-config/:modifierConfigId',
        component: ModifierConfigDetailPage
    },
    {
        name: "AdminModifierActivationRequirementList",
        path: 'modifier-activation-requirement-list',
        component: ModifierActivationRequirementListPage
    },
    {
        name: "AdminModifierActivationRequirementDetail",
        path: 'modifier-activation-requirement/:modifierActivationRequirementId',
        component: ModifierActivationRequirementDetailPage
    },
    {
        name: "AdminStatusConfigList",
        path: 'status-config-list',
        component: StatusConfigListPage
    },
    {
        name: "AdminStatusConfigDetail",
        path: 'status-config/:statusConfigId',
        component: StatusConfigDetailPage
    },
    {
        name: "AdminActionCostList",
        path: 'action-cost-list',
        component: ActionCostListPage
    },
    {
        name: "AdminActionCostDetail",
        path: 'action-cost/:actionCostId',
        component: ActionCostDetailPage
    },
    {
        name: "AdminActionConfigList",
        path: 'action-config-list',
        component: ActionConfigListPage
    },
    {
        name: "AdminActionConfigDetail",
        path: 'action-config/:actionConfigId',
        component: ActionConfigDetailPage
    },
    {
        name: "AdminDaedalusConfigList",
        path: 'daedalus-config-list',
        component: DaedalusConfigListPage
    },
    {
        name: "AdminDaedalusConfigDetail",
        path: 'daedalus-config/:daedalusConfigId',
        component: DaedalusConfigDetailPage
    },
    {
        name: "AdminDifficultyConfigList",
        path: 'difficulty-config-list',
        component: DifficultyConfigListPage
    },
    {
        name: "AdminDifficultyConfigDetail",
        path: 'difficulty-config/:difficultyConfigId',
        component: DifficultyConfigDetailPage
    },
    {
        name: "AdminCharacterConfigList",
        path: 'character-config-list',
        component: CharacterConfigListPage
    },
    {
        name: "AdminCharacterConfigDetail",
        path: 'character-config/:characterConfigId',
        component: CharacterConfigDetailPage
    },
    {
        name: "AdminDiseaseConfigList",
        path: 'disease-config-list',
        component: DiseaseConfigListPage
    },
    {
        name: "AdminDiseaseConfigDetail",
        path: 'disease-config/:diseaseConfigId',
        component: DiseaseConfigDetailPage
    },
    {
        name: "AdminSymptomConfigList",
        path: 'symptom-config-list',
        component: SymptomConfigListPage
    },
    {
        name: "AdminSymptomConfigDetail",
        path: 'symptom-config/:symptomConfigId',
        component: SymptomConfigDetailPage
    },
    {
        name: "AdminSymptomActivationRequirementList",
        path: 'symptom-activation-requirement-list',
        component: SymptomActivationRequirementListPage
    },
    {
        name: "AdminSymptomActivationRequirementDetail",
        path: 'symptom-activation-requirement/:symptomActivationRequirementId',
        component: SymptomActivationRequirementDetailPage
    },
    {
        name: "AdminEquipmentConfigList",
        path: 'equipment-config-list',
        component: EquipmentConfigListPage
    },
    {
        name: "AdminEquipmentConfigDetail",
        path: 'equipment-config/:equipmentConfigId',
        component: EquipmentConfigDetailPage
    },
    {
        name: "AdminPlaceConfigList",
        path: 'place-config-list',
        component: PlaceConfigListPage
    },
    {
        name: "AdminPlaceConfigDetail",
        path: 'place-config/:placeConfigId',
        component: PlaceConfigDetailPage
    },
    {
        name: "AdminRandomItemPlacesList",
        path: 'random-item-places-list',
        component: RandomItemPlacesListPage
    },
    {
        name: "AdminRandomItemPlacesDetail",
        path: 'random-item-places/:randomItemPlacesId',
        component: RandomItemPlacesDetailPage
    },
    {
        name: "AdminMechanicsList",
        path: 'mechanics-list',
        component: MechanicsListPage
    },
    {
        name: "AdminMechanicsDetail",
        path: 'mechanics/:mechanicsId',
        component: MechanicsDetailPage
    },
];
