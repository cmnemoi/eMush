import GameConfigListPage from "@/components/Admin/Config/GameConfig/GameConfigListPage.vue";
import GameConfigDetailPage from "@/components/Admin/Config/GameConfig/GameConfigDetailPage.vue";
import ModifierConfigListPage from "@/components/Admin/Config/ModifierConfig/ModifierConfigListPage.vue";
import ModifierActivationRequirementDetailPage from "@/components/Admin/Config/ModifierConfig/ModifierActivationRequirementDetailPage.vue";
import StatusConfigListPage from "@/components/Admin/Config/StatusConfig/StatusConfigListPage.vue";
import StatusConfigDetailPage from "@/components/Admin/Config/StatusConfig/StatusConfigDetailPage.vue";
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
import EquipmentConfigListPage from "@/components/Admin/Config/EquipmentConfig/EquipmentConfigListPage.vue";
import EquipmentConfigDetailPage from "@/components/Admin/Config/EquipmentConfig/EquipmentConfigDetailPage.vue";
import PlaceConfigDetailPage from "@/components/Admin/Config/PlaceConfig/PlaceConfigDetailPage.vue";
import PlaceConfigListPage from "@/components/Admin/Config/PlaceConfig/PlaceConfigListPage.vue";
import RandomItemPlacesListPage from "@/components/Admin/Config/RandomItemPlaces/RandomItemPlacesListPage.vue";
import RandomItemPlacesDetailPage from "@/components/Admin/Config/RandomItemPlaces/RandomItemPlacesDetailPage.vue";
import MechanicsListPage from "@/components/Admin/Config/Mechanics/MechanicsListPage.vue";
import MechanicsDetailPage from "@/components/Admin/Config/Mechanics/MechanicsDetailPage.vue";
import ConsumableDiseaseConfigListPage from "@/components/Admin/Config/ConsumableDiseaseConfig/ConsumableDiseaseConfigListPage.vue";
import ConsumableDiseaseConfigDetailPage from "@/components/Admin/Config/ConsumableDiseaseConfig/ConsumableDiseaseConfigDetailPage.vue";
import DiseaseCauseConfigListPage from "@/components/Admin/Config/DiseaseCauseConfig/DiseaseCauseConfigListPage.vue";
import DiseaseCauseConfigDetailPage from "@/components/Admin/Config/DiseaseCauseConfig/DiseaseCauseConfigDetailPage.vue";
import TriumphConfigListPage from "@/components/Admin/Config/TriumphConfig/TriumphConfigListPage.vue";
import TriumphConfigDetailPage from "@/components/Admin/Config/TriumphConfig/TriumphConfigDetailPage.vue";
import DirectModifierDetailPage from "@/components/Admin/Config/ModifierConfig/DirectModifierDetailPage.vue";
import VariableEventDetailPage from "@/components/Admin/Config/EventConfig/VariableEventDetailPage.vue";
import EventConfigListPage from "@/components/Admin/Config/EventConfig/EventConfigListPage.vue";
import EventModifierDetailPage from "@/components/Admin/Config/ModifierConfig/EventModifierDetailPage.vue";
import HunterConfigListPage from "@/components/Admin/Config/HunterConfig/HunterConfigListPage.vue";
import HunterConfigDetailPage from "@/components/Admin/Config/HunterConfig/HunterConfigDetailPage.vue";

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
        name: "AdminEventModifierConfigDetail",
        path: 'event-modifier-config/:configId',
        component: EventModifierDetailPage
    },
    {
        name: "AdminDirectModifierConfigDetail",
        path: 'direct-modifier-config/:configId',
        component: DirectModifierDetailPage
    },
    {
        name: "AdminModifierActivationRequirementDetail",
        path: 'modifier-activation-requirement/:configId',
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
    {
        name: "AdminConsumableDiseaseConfigList",
        path: 'consumable-disease-config-list',
        component: ConsumableDiseaseConfigListPage
    },
    {
        name: "AdminConsumableDiseaseConfigDetail",
        path: 'consumable-disease-config/:consumableDiseaseConfigId',
        component: ConsumableDiseaseConfigDetailPage
    },
    {
        name: "AdminDiseaseCauseConfigList",
        path: 'disease-cause-config-list',
        component: DiseaseCauseConfigListPage
    },
    {
        name: "AdminDiseaseCauseConfigDetail",
        path: 'disease-cause-config/:diseaseCauseConfigId',
        component: DiseaseCauseConfigDetailPage
    },
    {
        name: "AdminTriumphConfigList",
        path: 'triumph-config-list',
        component: TriumphConfigListPage
    },
    {
        name: "AdminTriumphConfigDetail",
        path: 'triumph-config/:triumphConfigId',
        component: TriumphConfigDetailPage
    },
    {
        name: "AdminEventConfigList",
        path: 'event-config-list',
        component: EventConfigListPage
    },
    {
        name: "AdminVariableEventConfigDetail",
        path: 'event-config/:configId',
        component: VariableEventDetailPage
    },
    {
        name: "AdminHunterConfigList",
        path: 'hunter-config-list',
        component: HunterConfigListPage
    },
    {
        name: "AdminHunterConfigDetail",
        path: 'hunter-config/:hunterConfigId',
        component: HunterConfigDetailPage
    },
];
