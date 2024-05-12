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
import { Mechanics } from "@/entities/Config/Mechanics";
import { PlaceConfig } from "@/entities/Config/PlaceConfig";
import { RandomItemPlaces } from "@/entities/Config/RandomItemPlaces";
import { ConsumableDiseaseConfig } from "@/entities/Config/ConsumableDiseaseConfig";
import { ConsumableDiseaseAttribute } from "@/entities/ConsumableDiseaseAttribute";
import { DiseaseCauseConfig } from "@/entities/Config/DiseaseCauseConfig";
import { TriumphConfig } from "@/entities/Config/TriumphConfig";
import { EventConfig } from "@/entities/Config/EventConfig";

// @ts-ignore
const GAME_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "game_configs");
// @ts-ignore
const MODIFIER_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "modifier_configs");
// @ts-ignore
const VARIABLE_MODIFIER_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "variable_event_modifier_configs");
// @ts-ignore
const TRIGGER_EVENT_MODIFIER_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "trigger_event_modifier_configs");
// @ts-ignore
const DIRECT_MODIFIER_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "direct_modifier_configs");
// @ts-ignore
const EVENT_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "event_configs");
// @ts-ignore
const VARIABLE_EVENT_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "variable_event_configs");
// @ts-ignore
const MODIFIER_REQUIREMENT_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "modifier_activation_requirements");
// @ts-ignore
const CONFIG_STATUS_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "status_configs");
// @ts-ignore
const ACTION_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "action_configs");
// @ts-ignore
const CONFIG_DAEDALUS_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "daedalus_configs");
// @ts-ignore
const DIFFICULTY_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "difficulty_configs");
// @ts-ignore
const CHARACTER_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "character_configs");
// @ts-ignore
const EQUIPMENT_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "equipment_configs");
// @ts-ignore
const DISEASE_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "disease_configs");
// @ts-ignore
const MECHANICS_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "mechanics");
// @ts-ignore
const PLACE_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "place_configs");
// @ts-ignore
const RANDOM_ITEM_PLACES_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "random_item_places");
// @ts-ignore
const CONSUMABLE_DISEASE_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "consumable_disease_configs");
// @ts-ignore
const CONSUMABLE_DISEASE_ATTRIBUTE_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "consumable_disease_attributes");
// @ts-ignore
const DISEASE_CAUSE_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "disease_cause_configs");
// @ts-ignore
const TRIUMPH_CONFIG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "triumph_configs");
// @ts-ignore
const BLUEPRINT_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "blueprints");
// @ts-ignore
const BOOK_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "books");
// @ts-ignore
const DOCUMENT_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "documents");
// @ts-ignore
const DRUG_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "drugs");
// @ts-ignore
const FRUIT_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "fruits");
// @ts-ignore
const GEAR_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "gears");
// @ts-ignore
const PLANT_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "plants");
// @ts-ignore
const RATION_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "rations");
// @ts-ignore
const WEAPON_ENDPOINTS = urlJoin(import.meta.env.VITE_APP_API_URL, "weapons");

const MECHANICS_ENDPOINTS: Map<string, string> = new Map([
    ['blueprint', BLUEPRINT_ENDPOINT],
    ['book', BOOK_ENDPOINT],
    ['document', DOCUMENT_ENDPOINT],
    ['drug', DRUG_ENDPOINT],
    ['fruit', FRUIT_ENDPOINT],
    ['gear', GEAR_ENDPOINT],
    ['plant', PLANT_ENDPOINT],
    ['ration', RATION_ENDPOINT],
    ['weapon', WEAPON_ENDPOINTS]
]);

const MODIFIER_CONFIG_ENDPOINTS: Map<string, string> = new Map([
    ['variableeventmodifierconfig', VARIABLE_MODIFIER_CONFIG_ENDPOINT],
    ['triggereventmodifierconfig', TRIGGER_EVENT_MODIFIER_CONFIG_ENDPOINT],
    ['directmodifierconfig', DIRECT_MODIFIER_CONFIG_ENDPOINT]
]);

const EVENT_CONFIG_ENDPOINTS: Map<string, string> = new Map([
    ['variableeventconfig', VARIABLE_EVENT_CONFIG_ENDPOINT]
]);


const GameConfigService = {
    loadGameConfig: async(gameConfigId: number): Promise<GameConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const gameConfigData = await ApiService.get(GAME_CONFIG_ENDPOINT + '/' + gameConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));
        await store.dispatch('gameConfig/setLoading', { loading: false });
        let gameConfig = null;
        if (gameConfigData.data) {
            gameConfig = (new GameConfig()).load(gameConfigData.data);
        }

        return gameConfig;
    },

    updateGameConfig: async(gameConfig: GameConfig): Promise<GameConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const gameConfigData = await ApiService.put(GAME_CONFIG_ENDPOINT + '/' + gameConfig.id, gameConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (gameConfigData.data) {
            gameConfig = (new GameConfig()).load(gameConfigData.data);
        }

        return gameConfig;
    },

    createModifierConfig: async(modifierConfig: ModifierConfig): Promise<ModifierConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierType = modifierConfig.type?.toLocaleLowerCase();
        if (modifierType === undefined) {
            throw new Error('Mechanics type is not defined');
        }

        const modifierConfigRecord: Record<string, any> = modifierConfig.jsonEncode();
        const modifierConfigData = await ApiService.post(MODIFIER_CONFIG_ENDPOINTS.get(modifierType) + '', modifierConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (modifierConfigData.data) {
            modifierConfig = (new ModifierConfig()).load(modifierConfigData.data);
        }

        return modifierConfig;

    },

    loadModifierConfig: async(modifierConfigId: number): Promise<ModifierConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });

        const modifierConfigData = await ApiService.get(MODIFIER_CONFIG_ENDPOINT + '/' + modifierConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let modifierConfig = null;
        if (modifierConfigData.data) {
            modifierConfig = (new ModifierConfig()).load(modifierConfigData.data);
        }

        return modifierConfig;
    },

    updateModifierConfig: async(modifierConfig: ModifierConfig): Promise<ModifierConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierType = modifierConfig.type?.toLocaleLowerCase();
        if (modifierType === undefined) {
            throw new Error('Mechanics type is not defined');
        }


        const modifierConfigData = await ApiService.put(MODIFIER_CONFIG_ENDPOINTS.get(modifierType) + '/' + modifierConfig.id, modifierConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (modifierConfigData.data) {
            modifierConfig = (new ModifierConfig()).load(modifierConfigData.data);
        }

        return modifierConfig;
    },

    createModifierActivationRequirement: async(modifierRequirement: ModifierActivationRequirement): Promise<ModifierActivationRequirement | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierRequirementRecord: Record<string, any> = modifierRequirement.jsonEncode();

        const modifierRequirementData = await ApiService.post(MODIFIER_REQUIREMENT_ENDPOINT, modifierRequirementRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (modifierRequirementData.data) {
            modifierRequirement = (new ModifierActivationRequirement()).load(modifierRequirementData.data);
        }

        return modifierRequirement;

    },

    loadModifierActivationRequirement: async(modifierRequirementId: number): Promise<ModifierActivationRequirement | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierRequirementData = await ApiService.get(MODIFIER_REQUIREMENT_ENDPOINT + '/' + modifierRequirementId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let modifierRequirement = null;
        if (modifierRequirementData.data) {
            modifierRequirement = (new ModifierActivationRequirement()).load(modifierRequirementData.data);
        }

        return modifierRequirement;
    },

    updateModifierActivationRequirement: async(modifierRequirement: ModifierActivationRequirement): Promise<ModifierActivationRequirement | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const modifierRequirementData = await ApiService.put(MODIFIER_REQUIREMENT_ENDPOINT + '/' + modifierRequirement.id, modifierRequirement)
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (modifierRequirementData.data) {
            modifierRequirement = (new ModifierActivationRequirement()).load(modifierRequirementData.data);
        }

        return modifierRequirement;
    },

    createStatusConfig: async(statusConfig: StatusConfig): Promise<StatusConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const statusConfigRecord: Record<string, any> = statusConfig.jsonEncode();

        const statusConfigData = await ApiService.post(CONFIG_STATUS_ENDPOINT, statusConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (statusConfigData.data) {
            statusConfig = (new StatusConfig()).load(statusConfigData.data);
        }

        return statusConfig;

    },

    loadStatusConfig: async(statusConfigId: number): Promise<StatusConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const statusConfigData = await ApiService.get(CONFIG_STATUS_ENDPOINT + '/' + statusConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let statusConfig = null;
        if (statusConfigData.data) {
            statusConfig = (new StatusConfig()).load(statusConfigData.data);
        }

        return statusConfig;
    },

    updateStatusConfig: async(statusConfig: StatusConfig): Promise<StatusConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const statusConfigData = await ApiService.put(CONFIG_STATUS_ENDPOINT + '/' + statusConfig.id, statusConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (statusConfigData.data) {
            statusConfig = (new StatusConfig()).load(statusConfigData.data);
        }

        return statusConfig;
    },

    createActionConfig: async(actionConfig: ActionConfig): Promise<ActionConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const actionConfigRecord : Record<string, any> = actionConfig.jsonEncode();

        const actionConfigData = await ApiService.post(ACTION_CONFIG_ENDPOINT, actionConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if(actionConfigData.data) {
            actionConfig = (new ActionConfig()).load(actionConfigData.data);
        }

        return actionConfig;
    },

    loadActionConfig: async(actionConfigId: number): Promise<ActionConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const actionConfigData = await ApiService.get(ACTION_CONFIG_ENDPOINT + '/' + actionConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let actionConfig = null;
        if (actionConfigData.data) {
            actionConfig = (new ActionConfig()).load(actionConfigData.data);
        }

        return actionConfig;
    },

    updateActionConfig: async(actionConfig: ActionConfig): Promise<ActionConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const actionConfigData = await ApiService.put(ACTION_CONFIG_ENDPOINT + '/' + actionConfig.id, actionConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (actionConfigData.data) {
            actionConfig = (new ActionConfig()).load(actionConfigData.data);
        }

        return actionConfig;
    },

    createDaedalusConfig: async(daedalusConfig: DaedalusConfig): Promise<DaedalusConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const daedalusConfigRecord: Record<string, any> = daedalusConfig.jsonEncode();

        const daedalusConfigData = await ApiService.post(CONFIG_DAEDALUS_CONFIG_ENDPOINT, daedalusConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (daedalusConfigData.data) {
            daedalusConfig = (new DaedalusConfig()).load(daedalusConfigData.data);
        }

        return daedalusConfig;
    },

    loadDaedalusConfig: async(daedalusConfigId: number): Promise<DaedalusConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const daedalusConfigData = await ApiService.get(CONFIG_DAEDALUS_CONFIG_ENDPOINT + '/' + daedalusConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let daedalusConfig = null;
        if (daedalusConfigData.data) {
            daedalusConfig = (new DaedalusConfig()).load(daedalusConfigData.data);
        }

        return daedalusConfig;
    },

    updateDaedalusConfig: async(daedalusConfig: DaedalusConfig): Promise<DaedalusConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const daedalusConfigData = await ApiService.put(CONFIG_DAEDALUS_CONFIG_ENDPOINT + '/' + daedalusConfig.id, daedalusConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (daedalusConfigData.data) {
            daedalusConfig = (new DaedalusConfig()).load(daedalusConfigData.data);
        }

        return daedalusConfig;
    },

    loadDifficultyConfig: async(difficultyConfigId: number): Promise<DifficultyConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const difficultyConfigData = await ApiService.get(DIFFICULTY_CONFIG_ENDPOINT + '/' + difficultyConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let difficultyConfig = null;
        if (difficultyConfigData.data) {
            difficultyConfig = (new DifficultyConfig()).load(difficultyConfigData.data);
        }

        return difficultyConfig;
    },

    updateDifficultyConfig: async(difficultyConfig: DifficultyConfig): Promise<DifficultyConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const difficultyConfigData = await ApiService.put(DIFFICULTY_CONFIG_ENDPOINT + '/' + difficultyConfig.id, difficultyConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (difficultyConfigData.data) {
            difficultyConfig = (new DifficultyConfig()).load(difficultyConfigData.data);
        }

        return difficultyConfig;
    },

    createCharacterConfig: async(characterConfig: CharacterConfig): Promise<CharacterConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const characterConfigRecord: Record<string, any> = characterConfig.jsonEncode();

        const characterConfigData = await ApiService.post(CHARACTER_CONFIG_ENDPOINT, characterConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (characterConfigData.data) {
            characterConfig = (new CharacterConfig()).load(characterConfigData.data);
        }

        return characterConfig;

    },

    loadCharacterConfig: async(characterConfigId: number): Promise<CharacterConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const characterConfigData = await ApiService.get(CHARACTER_CONFIG_ENDPOINT + '/' + characterConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let characterConfig = null;
        if (characterConfigData.data) {
            characterConfig = (new CharacterConfig()).load(characterConfigData.data);
        }

        return characterConfig;
    },

    updateCharacterConfig: async(characterConfig: CharacterConfig): Promise<CharacterConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const characterConfigData = await ApiService.put(CHARACTER_CONFIG_ENDPOINT + '/' + characterConfig.id, characterConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (characterConfigData.data) {
            characterConfig = (new CharacterConfig()).load(characterConfigData.data);
        }

        return characterConfig;
    },

    createEquipmentConfig: async(equipmentConfig: EquipmentConfig): Promise<EquipmentConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const characterConfigRecord : Record<string, any> = equipmentConfig.jsonEncode();

        const equipmentConfigData = await ApiService.post(EQUIPMENT_CONFIG_ENDPOINT, characterConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if(equipmentConfigData.data) {
            equipmentConfig = (new EquipmentConfig()).load(equipmentConfigData.data);
        }

        return equipmentConfig;

    },

    loadEquipmentConfig: async(equipmentConfigId: number): Promise<EquipmentConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const equipmentConfigData = await ApiService.get(EQUIPMENT_CONFIG_ENDPOINT + '/' + equipmentConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let equipmentConfig = null;
        if (equipmentConfigData.data) {
            equipmentConfig = (new EquipmentConfig()).load(equipmentConfigData.data);
        }

        return equipmentConfig;
    },

    updateEquipmentConfig: async(equipmentConfig: EquipmentConfig): Promise<EquipmentConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const equipmentConfigData = await ApiService.put(EQUIPMENT_CONFIG_ENDPOINT + '/' + equipmentConfig.id, equipmentConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (equipmentConfigData.data) {
            equipmentConfig = (new EquipmentConfig()).load(equipmentConfigData.data);
        }

        return equipmentConfig;
    },

    createDiseaseConfig: async (diseaseConfig: DiseaseConfig): Promise<DiseaseConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const diseaseConfigRecord: Record<string, any> = diseaseConfig.jsonEncode();

        const diseaseConfigData = await ApiService.post(DISEASE_CONFIG_ENDPOINT, diseaseConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (diseaseConfigData.data) {
            diseaseConfig = (new DiseaseConfig()).load(diseaseConfigData.data);
        }

        return diseaseConfig;
    },

    loadDiseaseConfig: async(diseaseConfigId: number): Promise<DiseaseConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const diseaseConfigData = await ApiService.get(DISEASE_CONFIG_ENDPOINT + '/' + diseaseConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let diseaseConfig = null;
        if (diseaseConfigData.data) {
            diseaseConfig = (new DiseaseConfig()).load(diseaseConfigData.data);
        }

        return diseaseConfig;
    },

    updateDiseaseConfig: async(diseaseConfig: DiseaseConfig): Promise<DiseaseConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const diseaseConfigData = await ApiService.put(DISEASE_CONFIG_ENDPOINT + '/' + diseaseConfig.id, diseaseConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (diseaseConfigData.data) {
            diseaseConfig = (new DiseaseConfig()).load(diseaseConfigData.data);
        }

        return diseaseConfig;
    },

    createMechanics: async (mechanics: Mechanics): Promise<Mechanics | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const mechanicsType = mechanics.mechanicsType?.toLocaleLowerCase();
        if (mechanicsType === undefined) {
            throw new Error('Mechanics type is not defined');
        }

        const mechanicsRecord: Record<string, any> = mechanics.jsonEncode();
        const mechanicsData = await ApiService.post(MECHANICS_ENDPOINTS.get(mechanicsType) + '', mechanicsRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (mechanicsData.data) {
            mechanics = (new Mechanics()).load(mechanicsData.data);
        }

        return mechanics;

    },

    loadMechanics: async(mechanicsId: number): Promise<Mechanics | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const mechanicsData = await ApiService.get(MECHANICS_ENDPOINT + '/' + mechanicsId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let mechanics = null;
        if (mechanicsData.data) {
            mechanics = (new Mechanics()).load(mechanicsData.data);
        }

        return mechanics;
    },

    updateMechanics: async(mechanics: Mechanics): Promise<Mechanics | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const mechanicsData = await ApiService.put(MECHANICS_ENDPOINT + '/' + mechanics.id, mechanics.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (mechanicsData.data) {
            mechanics = (new Mechanics()).load(mechanicsData.data);
        }

        return mechanics;
    },

    createPlaceConfig: async (placeConfig: PlaceConfig): Promise<PlaceConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const placeConfigRecord: Record<string, any> = placeConfig.jsonEncode();

        const placeConfigData = await ApiService.post(PLACE_CONFIG_ENDPOINT, placeConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (placeConfigData.data) {
            placeConfig = (new PlaceConfig()).load(placeConfigData.data);
        }

        return placeConfig;

    },

    loadPlaceConfig: async(placeConfigId: number): Promise<PlaceConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const placeConfigData = await ApiService.get(PLACE_CONFIG_ENDPOINT + '/' + placeConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let placeConfig = null;
        if (placeConfigData.data) {
            placeConfig = (new PlaceConfig()).load(placeConfigData.data);
        }

        return placeConfig;
    },

    updatePlaceConfig: async(placeConfig: PlaceConfig): Promise<PlaceConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const placeConfigData = await ApiService.put(PLACE_CONFIG_ENDPOINT + '/' + placeConfig.id, placeConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (placeConfigData.data) {
            placeConfig = (new PlaceConfig()).load(placeConfigData.data);
        }

        return placeConfig;
    },

    createRandomItemPlaces: async (randomItemPlaces: RandomItemPlaces): Promise<RandomItemPlaces | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const randomItemPlacesRecord: Record<string, any> = randomItemPlaces.jsonEncode();

        const randomItemPlacesData = await ApiService.post(RANDOM_ITEM_PLACES_ENDPOINT, randomItemPlacesRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (randomItemPlacesData.data) {
            randomItemPlaces = (new RandomItemPlaces()).load(randomItemPlacesData.data);
        }

        return randomItemPlaces;

    },

    loadRandomItemPlaces: async(randomItemPlacesId: number): Promise<RandomItemPlaces | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const randomItemPlacesData = await ApiService.get(RANDOM_ITEM_PLACES_ENDPOINT + '/' + randomItemPlacesId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let randomItemPlaces = null;
        if (randomItemPlacesData.data) {
            randomItemPlaces = (new RandomItemPlaces()).load(randomItemPlacesData.data);
        }

        return randomItemPlaces;
    },

    updateRandomItemPlaces: async(randomItemPlaces: RandomItemPlaces): Promise<RandomItemPlaces | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const randomItemPlacesData = await ApiService.put(RANDOM_ITEM_PLACES_ENDPOINT + '/' + randomItemPlaces.id, randomItemPlaces.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (randomItemPlacesData.data) {
            randomItemPlaces = (new RandomItemPlaces()).load(randomItemPlacesData.data);
        }

        return randomItemPlaces;
    },

    createConsumableDiseaseConfig: async(consumableDiseaseConfig: ConsumableDiseaseConfig): Promise<ConsumableDiseaseConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const consumableDiseaseRecord : Record<string, any> = consumableDiseaseConfig.jsonEncode();

        const consumableDiseaseConfigData = await ApiService.post(CONSUMABLE_DISEASE_CONFIG_ENDPOINT, consumableDiseaseRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (consumableDiseaseConfigData.data) {
            consumableDiseaseConfig = (new ConsumableDiseaseConfig()).load(consumableDiseaseConfigData.data);
        }

        return consumableDiseaseConfig;

    },

    loadConsumableDiseaseConfig: async(consumableDiseaseConfigId: number): Promise<ConsumableDiseaseConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const consumableDiseaseConfigData = await ApiService.get(CONSUMABLE_DISEASE_CONFIG_ENDPOINT + '/' + consumableDiseaseConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let consumableDiseaseConfig = null;
        if (consumableDiseaseConfigData.data) {
            consumableDiseaseConfig = (new ConsumableDiseaseConfig()).load(consumableDiseaseConfigData.data);
        }

        return consumableDiseaseConfig;
    },

    updateConsumableDiseaseConfig: async(consumableDiseaseConfig: ConsumableDiseaseConfig): Promise<ConsumableDiseaseConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const consumableDiseaseConfigData = await ApiService.put(CONSUMABLE_DISEASE_CONFIG_ENDPOINT + '/' + consumableDiseaseConfig.id, consumableDiseaseConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (consumableDiseaseConfigData.data) {
            consumableDiseaseConfig = (new ConsumableDiseaseConfig()).load(consumableDiseaseConfigData.data);
        }

        return consumableDiseaseConfig;
    },

    loadConsumableDiseaseAttribute: async(consumableDiseaseAttributeId: number): Promise<ConsumableDiseaseAttribute | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const consumableDiseaseAttributeData = await ApiService.get(CONSUMABLE_DISEASE_ATTRIBUTE_ENDPOINT + '/' + consumableDiseaseAttributeId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let consumableDiseaseAttribute = null;
        if (consumableDiseaseAttributeData.data) {
            consumableDiseaseAttribute = (new ConsumableDiseaseAttribute()).load(consumableDiseaseAttributeData.data);
        }

        return consumableDiseaseAttribute;
    },

    updateConsumableDiseaseAttribute: async(consumableDiseaseAttribute: ConsumableDiseaseAttribute): Promise<ConsumableDiseaseAttribute | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const consumableDiseaseAttributeData = await ApiService.put(CONSUMABLE_DISEASE_ATTRIBUTE_ENDPOINT + '/' + consumableDiseaseAttribute.id, consumableDiseaseAttribute.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (consumableDiseaseAttributeData.data) {
            consumableDiseaseAttribute = (new ConsumableDiseaseAttribute()).load(consumableDiseaseAttributeData.data);
        }

        return consumableDiseaseAttribute;
    },

    createDiseaseCauseConfig: async(diseaseCauseConfig: DiseaseCauseConfig): Promise<DiseaseCauseConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const diseaseCauseRecord : Record<string, any> = diseaseCauseConfig.jsonEncode();

        const diseaseCauseConfigData = await ApiService.post(DISEASE_CAUSE_CONFIG_ENDPOINT, diseaseCauseRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (diseaseCauseConfigData.data) {
            diseaseCauseConfig = (new DiseaseCauseConfig()).load(diseaseCauseConfigData.data);
        }

        return diseaseCauseConfig;

    },

    loadDiseaseCauseConfig: async(diseaseCauseConfigId: number): Promise<DiseaseCauseConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const diseaseCauseConfigData = await ApiService.get(DISEASE_CAUSE_CONFIG_ENDPOINT + '/' + diseaseCauseConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let diseaseCauseConfig = null;
        if (diseaseCauseConfigData.data) {
            diseaseCauseConfig = (new DiseaseCauseConfig()).load(diseaseCauseConfigData.data);
        }

        return diseaseCauseConfig;
    },

    updateDiseaseCauseConfig: async(diseaseCauseConfig: DiseaseCauseConfig): Promise<DiseaseCauseConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const diseaseCauseConfigData = await ApiService.put(DISEASE_CAUSE_CONFIG_ENDPOINT + '/' + diseaseCauseConfig.id, diseaseCauseConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (diseaseCauseConfigData.data) {
            diseaseCauseConfig = (new DiseaseCauseConfig()).load(diseaseCauseConfigData.data);
        }

        return diseaseCauseConfig;
    },

    createTriumphConfig: async(triumphConfig: TriumphConfig): Promise<TriumphConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const triumphConfigRecord : Record<string, any> = triumphConfig.jsonEncode();

        const triumphConfigData = await ApiService.post(TRIUMPH_CONFIG_ENDPOINT, triumphConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (triumphConfigData.data) {
            triumphConfig = (new TriumphConfig()).load(triumphConfigData.data);
        }

        return triumphConfig;
    },

    loadTriumphConfig: async(triumphConfigId: number): Promise<TriumphConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const triumphConfigData = await ApiService.get(TRIUMPH_CONFIG_ENDPOINT + '/' + triumphConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let triumphConfig = null;
        if (triumphConfigData.data) {
            triumphConfig = (new TriumphConfig()).load(triumphConfigData.data);
        }

        return triumphConfig;
    },

    updateTriumphConfig: async(triumphConfig: TriumphConfig): Promise<TriumphConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const triumphConfigData = await ApiService.put(TRIUMPH_CONFIG_ENDPOINT + '/' + triumphConfig.id, triumphConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (triumphConfigData.data) {
            triumphConfig = (new TriumphConfig()).load(triumphConfigData.data);
        }

        return triumphConfig;
    },

    createEventConfig: async(eventConfig: EventConfig): Promise<EventConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const eventType = eventConfig.type?.toLocaleLowerCase();
        if (eventType === undefined) {
            throw new Error('eventConfig type is not defined');
        }

        const eventConfigRecord: Record<string, any> = eventConfig.jsonEncode();
        const eventConfigData = await ApiService.post(EVENT_CONFIG_ENDPOINTS.get(eventType) + '', eventConfigRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        if (eventConfigData.data) {
            eventConfig = (new EventConfig()).load(eventConfigData.data);
        }

        return eventConfig;

    },

    loadEventConfig: async(eventConfigId: number): Promise<EventConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });

        const eventConfigData = await ApiService.get(EVENT_CONFIG_ENDPOINT + '/' + eventConfigId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let eventConfig = null;
        if (eventConfigData.data) {
            eventConfig = (new EventConfig()).load(eventConfigData.data);
        }

        return eventConfig;
    },

    updateEventConfig: async(eventConfig: EventConfig): Promise<EventConfig | null> => {
        await store.dispatch('gameConfig/setLoading', { loading: true });
        const eventType = eventConfig.type?.toLocaleLowerCase();
        if (eventType === undefined) {
            throw new Error('Event type is not defined');
        }

        const eventConfigData = await ApiService.put(EVENT_CONFIG_ENDPOINTS.get(eventType) + '/' + eventConfig.id, eventConfig.jsonEncode())
            .catch((e) => {
                store.dispatch('gameConfig/setLoading', { loading: false });
                throw e;
            });

        await store.dispatch('gameConfig/setLoading', { loading: false });

        if (eventConfigData.data) {
            eventConfig = (new EventConfig()).load(eventConfigData.data);
        }

        return eventConfig;
    }
};
export default GameConfigService;
