import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { GameConfig } from "@/entities/Config/GameConfig";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";
import { ModifierCondition } from "@/entities/Config/ModifierCondition";
import { ModifierConfig } from "@/entities/Config/ModifierConfig";
import { StatusConfig } from "@/entities/Config/StatusConfig";
import store from "@/store";
import { ActionCost } from "@/entities/Config/ActionCost";
import { ActionConfig } from "@/entities/Config/ActionConfig";
import { DifficultyConfig } from "@/entities/Config/DifficultyConfig";
import { CharacterConfig } from "@/entities/Config/CharacterConfig";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";
import { DiseaseConfig } from "@/entities/Config/DiseaseConfig";
import { SymptomConfig } from "@/entities/Config/SymptomConfig";
import { SymptomCondition } from "@/entities/Config/SymptomCondition";
import { Mechanics } from "@/entities/Config/Mechanics";
import { PlaceConfig } from "@/entities/Config/PlaceConfig";

// @ts-ignore
const GAME_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "game_configs");
// @ts-ignore
const MODIFIER_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "modifier_configs");
// @ts-ignore
const MODIFIER_CONDITION_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "modifier_conditions");
// @ts-ignore
const CONFIG_STATUS_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "status_configs");
// @ts-ignore
const CONFIG_ACTION_COST_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "action_costs");
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
const SYMPTOM_CONDITION_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "symptom_conditions");
// @ts-ignore
const MECHANICS_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "mechanics");
// @ts-ignore
const PLACE_CONFIG_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "place_configs");

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

    loadModifierCondition: async(modifierConditionId: number): Promise<ModifierCondition | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierConditionData = await ApiService.get(MODIFIER_CONDITION_ENDPOINT + '/' + modifierConditionId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let modifierCondition = null;
        if (modifierConditionData.data) {
            modifierCondition = (new ModifierCondition()).load(modifierConditionData.data);
        }

        return modifierCondition;
    },

    updateModifierCondition: async(modifierCondition: ModifierCondition): Promise<ModifierCondition | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierConditionData = await ApiService.put(MODIFIER_CONDITION_ENDPOINT + '/' + modifierCondition.id + '?XDEBUG_SESSION_START=PHPSTORM', modifierCondition)
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (modifierConditionData.data) {
            modifierCondition = (new ModifierCondition()).load(modifierConditionData.data);
        }

        return modifierCondition;
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

    loadActionCost: async(actionCostId: number): Promise<ActionCost | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const actionCostData = await ApiService.get(CONFIG_ACTION_COST_ENDPOINT + '/' + actionCostId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let actionCost = null;
        if (actionCostData.data) {
            actionCost = (new ActionCost()).load(actionCostData.data);
        }

        return actionCost;
    },

    updateActionCost: async(actionCost: ActionCost): Promise<ActionCost | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const actionCostData = await ApiService.put(CONFIG_ACTION_COST_ENDPOINT + '/' + actionCost.id + '?XDEBUG_SESSION_START=PHPSTORM', actionCost.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (actionCostData.data) {
            actionCost = (new ActionCost()).load(actionCostData.data);
        }

        return actionCost;
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

    loadSymptomCondition: async(symptomConditionId: number): Promise<SymptomCondition | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const symptomConditionData = await ApiService.get(SYMPTOM_CONDITION_ENDPOINT + '/' + symptomConditionId + '?XDEBUG_SESSION_START=PHPSTORM')
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let symptomCondition = null;
        if (symptomConditionData.data) {
            symptomCondition = (new SymptomCondition()).load(symptomConditionData.data);
        }

        return symptomCondition;
    },

    updateSymptomCondition: async(symptomCondition: SymptomCondition): Promise<SymptomCondition | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });
        const symptomConditionData = await ApiService.put(SYMPTOM_CONDITION_ENDPOINT + '/' + symptomCondition.id + '?XDEBUG_SESSION_START=PHPSTORM', symptomCondition.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        store.dispatch('gameConfig/setLoading', { loading: false });

        if (symptomConditionData.data) {
            symptomCondition = (new SymptomCondition()).load(symptomConditionData.data);
        }

        return symptomCondition;
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
    }
};
export default GameConfigService;
