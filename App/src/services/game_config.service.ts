import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { GameConfig } from "@/entities/Config/GameConfig";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";
import { ModifierActivationRequirement } from "@/entities/Config/ModifierActivationRequirement";
import { ModifierConfig } from "@/entities/Config/ModifierConfig";
import { StatusConfig } from "@/entities/Config/StatusConfig";
import store from "@/store";
import { ActionConfig } from "@/entities/Config/ActionConfig";
import { DifficultyConfig } from "@/entities/Config/DifficultyConfig";
import { CharacterConfig } from "@/entities/Config/CharacterConfig";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";
import { DiseaseConfig } from "@/entities/Config/DiseaseConfig";
import { SymptomConfig } from "@/entities/Config/SymptomConfig";
import { SymptomActivationRequirement } from "@/entities/Config/SymptomActivationRequirement";
import { Mechanics } from "@/entities/Config/Mechanics";
import { PlaceConfig } from "@/entities/Config/PlaceConfig";
import { RandomItemPlaces } from "@/entities/Config/RandomItemPlaces";
import { ConsumableDiseaseConfig } from "@/entities/Config/ConsumableDiseaseConfig";
import { ConsumableDiseaseAttribute } from "@/entities/ConsumableDiseaseAttribute";
import { DiseaseCauseConfig } from "@/entities/Config/DiseaseCauseConfig";
import { TriumphConfig } from "@/entities/Config/TriumphConfig";

// @ts-ignore
const GAME_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "game_configs");
// @ts-ignore
const MODIFIER_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "modifier_configs");
// @ts-ignore
const MODIFIER_REQUIREMENT_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "modifier_activation_requirements");
// @ts-ignore
const CONFIG_STATUS_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "status_configs");
// @ts-ignore
const CONFIG_ACTION_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "actions");
// @ts-ignore
const CONFIG_DAEDALUS_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "daedalus_configs");
// @ts-ignore
const DIFFICULTY_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "difficulty_configs");
// @ts-ignore
const CHARACTER_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "character_configs");
// @ts-ignore
const EQUIPMENT_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "equipment_configs");
// @ts-ignore
const DISEASE_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "disease_configs");
// @ts-ignore
const SYMPTOM_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "symptom_configs");
// @ts-ignore
const SYMPTOM_REQUIREMENT_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "symptom_activation_requirements");
// @ts-ignore
const MECHANICS_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "mechanics");
// @ts-ignore
const PLACE_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "place_configs");
// @ts-ignore
const RANDOM_ITEM_PLACES_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "random_item_places");
// @ts-ignore
const CONSUMABLE_DISEASE_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "consumable_disease_configs");
// @ts-ignore
const CONSUMABLE_DISEASE_ATTRIBUTE_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "consumable_disease_attributes");
// @ts-ignore
const DISEASE_CAUSE_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "disease_cause_configs");
// @ts-ignore
const TRIUMPH_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "triumph_configs");
// @ts-ignore
const BLUEPRINT_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "blueprints");

const MECHANICS_ENDPOINTS: Map<string, string> = new Map([
    ['blueprint', BLUEPRINT_ENDPOINT],
]);
    

const GameConfigService = {
    loadGameConfig: async(gameConfigId: number): Promise<GameConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const gameConfigData = await ApiService.get(GAME_CONFIG_ENDPOINT + '/' + gameConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));
        store.dispatch('gameConfig/setLoading', { loading: false });
        let gameConfig = null;
        if (gameConfigData.data) {
            gameConfig = (new GameConfig()).load(gameConfigData.data);
        }

        return gameConfig;
    },

    updateGameConfig: async(gameConfig: GameConfig): Promise<GameConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const gameConfigData = await ApiService.put(GAME_CONFIG_ENDPOINT + '/' + gameConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', gameConfig)
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (gameConfigData.data) {
            gameConfig = (new GameConfig()).load(gameConfigData.data);
        }

        return gameConfig;
    },

    loadModifierConfig: async(modifierConfigId: number): Promise<ModifierConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierConfigData = await ApiService.get(MODIFIER_CONFIG_ENDPOINT + '/' + modifierConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let modifierConfig = null;
        if (modifierConfigData.data) {
            modifierConfig = (new ModifierConfig()).load(modifierConfigData.data);
        }

        return modifierConfig;
    },

    updateModifierConfig: async(modifierConfig: ModifierConfig): Promise<ModifierConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierConfigData = await ApiService.put(MODIFIER_CONFIG_ENDPOINT + '/' + modifierConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', modifierConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (modifierConfigData.data) {
            modifierConfig = (new ModifierConfig()).load(modifierConfigData.data);
        }

        return modifierConfig;
    },

    loadModifierActivationRequirement: async(modifierRequirementId: number): Promise<ModifierActivationRequirement | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierRequirementData = await ApiService.get(MODIFIER_REQUIREMENT_ENDPOINT + '/' + modifierRequirementId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let modifierRequirement = null;
        if (modifierRequirementData.data) {
            modifierRequirement = (new ModifierActivationRequirement()).load(modifierRequirementData.data);
        }

        return modifierRequirement;
    },

    updateModifierActivationRequirement: async(modifierRequirement: ModifierActivationRequirement): Promise<ModifierActivationRequirement | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierRequirementData = await ApiService.put(MODIFIER_REQUIREMENT_ENDPOINT + '/' + modifierRequirement.id + '?XDEBUG_SESSION_START=PHPSTORM', modifierRequirement)
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (modifierRequirementData.data) {
            modifierRequirement = (new ModifierActivationRequirement()).load(modifierRequirementData.data);
        }

        return modifierRequirement;
    },

    loadStatusConfig: async(statusConfigId: number): Promise<StatusConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const statusConfigData = await ApiService.get(CONFIG_STATUS_ENDPOINT + '/' + statusConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let statusConfig = null;
        if (statusConfigData.data) {
            statusConfig = (new StatusConfig()).load(statusConfigData.data);
        }

        return statusConfig;
    },

    updateStatusConfig: async(statusConfig: StatusConfig): Promise<StatusConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const statusConfigData = await ApiService.put(CONFIG_STATUS_ENDPOINT + '/' + statusConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', statusConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (statusConfigData.data) {
            statusConfig = (new StatusConfig()).load(statusConfigData.data);
        }

        return statusConfig;
    },

    loadActionConfig: async(actionConfigId: number): Promise<ActionConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const actionConfigData = await ApiService.get(CONFIG_ACTION_CONFIG_ENDPOINT + '/' + actionConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let actionConfig = null;
        if (actionConfigData.data) {
            actionConfig = (new ActionConfig()).load(actionConfigData.data);
        }

        return actionConfig;
    },

    updateActionConfig: async(actionConfig: ActionConfig): Promise<ActionConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const actionConfigData = await ApiService.put(CONFIG_ACTION_CONFIG_ENDPOINT + '/' + actionConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', actionConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (actionConfigData.data) {
            actionConfig = (new ActionConfig()).load(actionConfigData.data);
        }

        return actionConfig;
    },

    loadDaedalusConfig: async(daedalusConfigId: number): Promise<DaedalusConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const daedalusConfigData = await ApiService.get(CONFIG_DAEDALUS_CONFIG_ENDPOINT + '/' + daedalusConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let daedalusConfig = null;
        if (daedalusConfigData.data) {
            daedalusConfig = (new DaedalusConfig()).load(daedalusConfigData.data);
        }

        return daedalusConfig;
    },

    updateDaedalusConfig: async(daedalusConfig: DaedalusConfig): Promise<DaedalusConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const daedalusConfigData = await ApiService.put(CONFIG_DAEDALUS_CONFIG_ENDPOINT + '/' + daedalusConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', daedalusConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (daedalusConfigData.data) {
            daedalusConfig = (new DaedalusConfig()).load(daedalusConfigData.data);
        }

        return daedalusConfig;
    },

    loadDifficultyConfig: async(difficultyConfigId: number): Promise<DifficultyConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const difficultyConfigData = await ApiService.get(DIFFICULTY_CONFIG_ENDPOINT + '/' + difficultyConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let difficultyConfig = null;
        if (difficultyConfigData.data) {
            difficultyConfig = (new DifficultyConfig()).load(difficultyConfigData.data);
        }

        return difficultyConfig;
    },

    updateDifficultyConfig: async(difficultyConfig: DifficultyConfig): Promise<DifficultyConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const difficultyConfigData = await ApiService.put(DIFFICULTY_CONFIG_ENDPOINT + '/' + difficultyConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', difficultyConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (difficultyConfigData.data) {
            difficultyConfig = (new DifficultyConfig()).load(difficultyConfigData.data);
        }

        return difficultyConfig;
    },

    loadCharacterConfig: async(characterConfigId: number): Promise<CharacterConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const characterConfigData = await ApiService.get(CHARACTER_CONFIG_ENDPOINT + '/' + characterConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let characterConfig = null;
        if (characterConfigData.data) {
            characterConfig = (new CharacterConfig()).load(characterConfigData.data);
        }

        return characterConfig;
    },

    updateCharacterConfig: async(characterConfig: CharacterConfig): Promise<CharacterConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const characterConfigData = await ApiService.put(CHARACTER_CONFIG_ENDPOINT + '/' + characterConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', characterConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (characterConfigData.data) {
            characterConfig = (new CharacterConfig()).load(characterConfigData.data);
        }

        return characterConfig;
    },

    createEquipmentConfig: async(equipmentConfig: EquipmentConfig): Promise<EquipmentConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const characterConfigRecord : Record<string, any> = equipmentConfig.jsonEncode();

        const equipmentConfigData = await ApiService.post(EQUIPMENT_CONFIG_ENDPOINT + '?XDEBUG_SESSION_START=PHPSTORM', characterConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if(equipmentConfigData.data) {
            equipmentConfig = (new EquipmentConfig()).load(equipmentConfigData.data);
        }

        return equipmentConfig;

    },

    loadEquipmentConfig: async(equipmentConfigId: number): Promise<EquipmentConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const equipmentConfigData = await ApiService.get(EQUIPMENT_CONFIG_ENDPOINT + '/' + equipmentConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let equipmentConfig = null;
        if (equipmentConfigData.data) {
            equipmentConfig = (new EquipmentConfig()).load(equipmentConfigData.data);
        }

        return equipmentConfig;
    },

    updateEquipmentConfig: async(equipmentConfig: EquipmentConfig): Promise<EquipmentConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const equipmentConfigData = await ApiService.put(EQUIPMENT_CONFIG_ENDPOINT + '/' + equipmentConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', equipmentConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (equipmentConfigData.data) {
            equipmentConfig = (new EquipmentConfig()).load(equipmentConfigData.data);
        }

        return equipmentConfig;
    },

    createDiseaseConfig: async (diseaseConfig: DiseaseConfig): Promise<DiseaseConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const diseaseConfigRecord: Record<string, any> = diseaseConfig.jsonEncode();

        const diseaseCauseConfigData = await ApiService.post(DISEASE_CONFIG_ENDPOINT + '?XDEBUG_SESSION_START=PHPSTORM', diseaseConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (diseaseCauseConfigData.data) {
            diseaseConfig = (new DiseaseConfig()).load(diseaseCauseConfigData.data);
        }

        return diseaseConfig;
    },

    loadDiseaseConfig: async(diseaseConfigId: number): Promise<DiseaseConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const diseaseConfigData = await ApiService.get(DISEASE_CONFIG_ENDPOINT + '/' + diseaseConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let diseaseConfig = null;
        if (diseaseConfigData.data) {
            diseaseConfig = (new DiseaseConfig()).load(diseaseConfigData.data);
        }

        return diseaseConfig;
    },

    updateDiseaseConfig: async(diseaseConfig: DiseaseConfig): Promise<DiseaseConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const diseaseConfigData = await ApiService.put(DISEASE_CONFIG_ENDPOINT + '/' + diseaseConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', diseaseConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (diseaseConfigData.data) {
            diseaseConfig = (new DiseaseConfig()).load(diseaseConfigData.data);
        }

        return diseaseConfig;
    },

    createSymptomConfig: async (symptomConfig: SymptomConfig): Promise<SymptomConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const symptomConfigRecord: Record<string, any> = symptomConfig.jsonEncode();

        const symptomConfigData = await ApiService.post(SYMPTOM_CONFIG_ENDPOINT + '?XDEBUG_SESSION_START=PHPSTORM', symptomConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (symptomConfigData.data) {
            symptomConfig = (new SymptomConfig()).load(symptomConfigData.data);
        }

        return symptomConfig;
    },

    loadSymptomConfig: async(symptomConfigId: number): Promise<SymptomConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const symptomConfigData = await ApiService.get(SYMPTOM_CONFIG_ENDPOINT + '/' + symptomConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let symptomConfig = null;
        if (symptomConfigData.data) {
            symptomConfig = (new SymptomConfig()).load(symptomConfigData.data);
        }

        return symptomConfig;
    },

    updateSymptomConfig: async(symptomConfig: SymptomConfig): Promise<SymptomConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const symptomConfigData = await ApiService.put(SYMPTOM_CONFIG_ENDPOINT + '/' + symptomConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', symptomConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (symptomConfigData.data) {
            symptomConfig = (new SymptomConfig()).load(symptomConfigData.data);
        }

        return symptomConfig;
    },

    createSymptomActivationRequirement: async (symptomActivationRequirement: SymptomActivationRequirement): Promise<SymptomActivationRequirement | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const symptomActivationRequirementRecord: Record<string, any> = symptomActivationRequirement.jsonEncode();

        const symptomActivationRequirementData = await ApiService.post(SYMPTOM_REQUIREMENT_ENDPOINT + '?XDEBUG_SESSION_START=PHPSTORM', symptomActivationRequirementRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (symptomActivationRequirementData.data) {
            symptomActivationRequirement = (new SymptomActivationRequirement()).load(symptomActivationRequirementData.data);
        }

        return symptomActivationRequirement;
    },

    loadSymptomActivationRequirement: async(symptomActivationRequirementId: number): Promise<SymptomActivationRequirement | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const symptomActivationRequirementData = await ApiService.get(SYMPTOM_REQUIREMENT_ENDPOINT + '/' + symptomActivationRequirementId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let symptomActivationRequirement = null;
        if (symptomActivationRequirementData.data) {
            symptomActivationRequirement = (new SymptomActivationRequirement()).load(symptomActivationRequirementData.data);
        }

        return symptomActivationRequirement;
    },

    updateSymptomActivationRequirement: async(symptomActivationRequirement: SymptomActivationRequirement): Promise<SymptomActivationRequirement | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const symptomActivationRequirementData = await ApiService.put(SYMPTOM_REQUIREMENT_ENDPOINT + '/' + symptomActivationRequirement.id + '?XDEBUG_SESSION_START=PHPSTORM', symptomActivationRequirement.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (symptomActivationRequirementData.data) {
            symptomActivationRequirement = (new SymptomActivationRequirement()).load(symptomActivationRequirementData.data);
        }

        return symptomActivationRequirement;
    },

    createMechanics: async (mechanics: Mechanics): Promise<Mechanics | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const mechanicsType = mechanics.mechanicsType?.toLocaleLowerCase();
        if (mechanicsType === undefined) {
            throw new Error('Mechanics type is not defined');
        }

        const mechanicsRecord: Record<string, any> = mechanics.jsonEncode();
        const mechanicsData = await ApiService.post(MECHANICS_ENDPOINTS.get(mechanicsType) + '?XDEBUG_SESSION_START=PHPSTORM', mechanicsRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (mechanicsData.data) {
            mechanics = (new Mechanics()).load(mechanicsData.data);
        }

        return mechanics;

    },

    loadMechanics: async(mechanicsId: number): Promise<Mechanics | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const mechanicsData = await ApiService.get(MECHANICS_ENDPOINT + '/' + mechanicsId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let mechanics = null;
        if (mechanicsData.data) {
            mechanics = (new Mechanics()).load(mechanicsData.data);
        }

        return mechanics;
    },

    updateMechanics: async(mechanics: Mechanics): Promise<Mechanics | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const mechanicsData = await ApiService.put(MECHANICS_ENDPOINT + '/' + mechanics.id + '?XDEBUG_SESSION_START=PHPSTORM', mechanics.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (mechanicsData.data) {
            mechanics = (new Mechanics()).load(mechanicsData.data);
        }

        return mechanics;
    },

    createPlaceConfig: async (placeConfig: PlaceConfig): Promise<PlaceConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const placeConfigRecord: Record<string, any> = placeConfig.jsonEncode();

        const placeConfigData = await ApiService.post(PLACE_CONFIG_ENDPOINT + '?XDEBUG_SESSION_START=PHPSTORM', placeConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (placeConfigData.data) {
            placeConfig = (new PlaceConfig()).load(placeConfigData.data);
        }

        return placeConfig;

    },

    loadPlaceConfig: async(placeConfigId: number): Promise<PlaceConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const placeConfigData = await ApiService.get(PLACE_CONFIG_ENDPOINT + '/' + placeConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let placeConfig = null;
        if (placeConfigData.data) {
            placeConfig = (new PlaceConfig()).load(placeConfigData.data);
        }

        return placeConfig;
    },

    updatePlaceConfig: async(placeConfig: PlaceConfig): Promise<PlaceConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const placeConfigData = await ApiService.put(PLACE_CONFIG_ENDPOINT + '/' + placeConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', placeConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (placeConfigData.data) {
            placeConfig = (new PlaceConfig()).load(placeConfigData.data);
        }

        return placeConfig;
    },

    createRandomItemPlaces: async (randomItemPlaces: RandomItemPlaces): Promise<RandomItemPlaces | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const randomItemPlacesRecord: Record<string, any> = randomItemPlaces.jsonEncode();

        const randomItemPlacesData = await ApiService.post(RANDOM_ITEM_PLACES_ENDPOINT + '?XDEBUG_SESSION_START=PHPSTORM', randomItemPlacesRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (randomItemPlacesData.data) {
            randomItemPlaces = (new RandomItemPlaces()).load(randomItemPlacesData.data);
        }

        return randomItemPlaces;

    },

    loadRandomItemPlaces: async(randomItemPlacesId: number): Promise<RandomItemPlaces | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const randomItemPlacesData = await ApiService.get(RANDOM_ITEM_PLACES_ENDPOINT + '/' + randomItemPlacesId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let randomItemPlaces = null;
        if (randomItemPlacesData.data) {
            randomItemPlaces = (new RandomItemPlaces()).load(randomItemPlacesData.data);
        }

        return randomItemPlaces;
    },

    updateRandomItemPlaces: async(randomItemPlaces: RandomItemPlaces): Promise<RandomItemPlaces | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const randomItemPlacesData = await ApiService.put(RANDOM_ITEM_PLACES_ENDPOINT + '/' + randomItemPlaces.id + '?XDEBUG_SESSION_START=PHPSTORM', randomItemPlaces.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (randomItemPlacesData.data) {
            randomItemPlaces = (new RandomItemPlaces()).load(randomItemPlacesData.data);
        }

        return randomItemPlaces;
    },

    createConsumableDiseaseConfig: async(consumableDiseaseConfig: ConsumableDiseaseConfig): Promise<ConsumableDiseaseConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const consumableDiseaseRecord : Record<string, any> = consumableDiseaseConfig.jsonEncode();

        const consumableDiseaseConfigData = await ApiService.post(CONSUMABLE_DISEASE_CONFIG_ENDPOINT + '?XDEBUG_SESSION_START=PHPSTORM', consumableDiseaseRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (consumableDiseaseConfigData.data) {
            consumableDiseaseConfig = (new ConsumableDiseaseConfig()).load(consumableDiseaseConfigData.data);
        }

        return consumableDiseaseConfig;

    },

    loadConsumableDiseaseConfig: async(consumableDiseaseConfigId: number): Promise<ConsumableDiseaseConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const consumableDiseaseConfigData = await ApiService.get(CONSUMABLE_DISEASE_CONFIG_ENDPOINT + '/' + consumableDiseaseConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let consumableDiseaseConfig = null;
        if (consumableDiseaseConfigData.data) {
            consumableDiseaseConfig = (new ConsumableDiseaseConfig()).load(consumableDiseaseConfigData.data);
        }

        return consumableDiseaseConfig;
    },

    updateConsumableDiseaseConfig: async(consumableDiseaseConfig: ConsumableDiseaseConfig): Promise<ConsumableDiseaseConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const consumableDiseaseConfigData = await ApiService.put(CONSUMABLE_DISEASE_CONFIG_ENDPOINT + '/' + consumableDiseaseConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', consumableDiseaseConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (consumableDiseaseConfigData.data) {
            consumableDiseaseConfig = (new ConsumableDiseaseConfig()).load(consumableDiseaseConfigData.data);
        }

        return consumableDiseaseConfig;
    },

    loadConsumableDiseaseAttribute: async(consumableDiseaseAttributeId: number): Promise<ConsumableDiseaseAttribute | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const consumableDiseaseAttributeData = await ApiService.get(CONSUMABLE_DISEASE_ATTRIBUTE_ENDPOINT + '/' + consumableDiseaseAttributeId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let consumableDiseaseAttribute = null;
        if (consumableDiseaseAttributeData.data) {
            consumableDiseaseAttribute = (new ConsumableDiseaseAttribute()).load(consumableDiseaseAttributeData.data);
        }

        return consumableDiseaseAttribute;
    },

    updateConsumableDiseaseAttribute: async(consumableDiseaseAttribute: ConsumableDiseaseAttribute): Promise<ConsumableDiseaseAttribute | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const consumableDiseaseAttributeData = await ApiService.put(CONSUMABLE_DISEASE_ATTRIBUTE_ENDPOINT + '/' + consumableDiseaseAttribute.id + '?XDEBUG_SESSION_START=PHPSTORM', consumableDiseaseAttribute.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (consumableDiseaseAttributeData.data) {
            consumableDiseaseAttribute = (new ConsumableDiseaseAttribute()).load(consumableDiseaseAttributeData.data);
        }

        return consumableDiseaseAttribute;
    },

    loadDiseaseCauseConfig: async(diseaseCauseConfigId: number): Promise<DiseaseCauseConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const diseaseCauseConfigData = await ApiService.get(DISEASE_CAUSE_CONFIG_ENDPOINT + '/' + diseaseCauseConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let diseaseCauseConfig = null;
        if (diseaseCauseConfigData.data) {
            diseaseCauseConfig = (new DiseaseCauseConfig()).load(diseaseCauseConfigData.data);
        }

        return diseaseCauseConfig;
    },

    updateDiseaseCauseConfig: async(diseaseCauseConfig: DiseaseCauseConfig): Promise<DiseaseCauseConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const diseaseCauseConfigData = await ApiService.put(DISEASE_CAUSE_CONFIG_ENDPOINT + '/' + diseaseCauseConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', diseaseCauseConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (diseaseCauseConfigData.data) {
            diseaseCauseConfig = (new DiseaseCauseConfig()).load(diseaseCauseConfigData.data);
        }

        return diseaseCauseConfig;
    },

    createTriumphConfig: async(triumphConfig: TriumphConfig): Promise<TriumphConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const triumphConfigRecord : Record<string, any> = triumphConfig.jsonEncode();

        const triumphConfigData = await ApiService.post(TRIUMPH_CONFIG_ENDPOINT + '?XDEBUG_SESSION_START=PHPSTORM', triumphConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (triumphConfigData.data) {
            triumphConfig = (new TriumphConfig()).load(triumphConfigData.data);
        }

        return triumphConfig;
    },

    loadTriumphConfig: async(triumphConfigId: number): Promise<TriumphConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const triumphConfigData = await ApiService.get(TRIUMPH_CONFIG_ENDPOINT + '/' + triumphConfigId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let triumphConfig = null;
        if (triumphConfigData.data) {
            triumphConfig = (new TriumphConfig()).load(triumphConfigData.data);
        }

        return triumphConfig;
    },

    updateTriumphConfig: async(triumphConfig: TriumphConfig): Promise<TriumphConfig | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const triumphConfigData = await ApiService.put(TRIUMPH_CONFIG_ENDPOINT + '/' + triumphConfig.id + '?XDEBUG_SESSION_START=PHPSTORM', triumphConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (triumphConfigData.data) {
            triumphConfig = (new TriumphConfig()).load(triumphConfigData.data);
        }

        return triumphConfig;
    }
};
export default GameConfigService;
