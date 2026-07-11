import ActionService from "@/services/action.service";
import { ActionTree, Commit, Dispatch, GetterTree, MutationTree } from "vuex";
import store from "@/store/index";
import { AxiosResponse } from "axios";
import { Action } from "@/entities/Action";
import { Hunter } from "@/entities/Hunter";
import { Terminal } from "@/entities/Terminal";
import { Planet } from "@/entities/Planet";
import { Item } from "@/entities/Item";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";

interface ActionResponseData {
    actionResult?: string;
    actionDetails?: {
        playerId?: number;
        hunterIsAlive?: boolean;
        targetedHunterId?: number;
    };
}

interface ActionModuleState {
    isHunterBeenHit: boolean;
    isHunterBeenKilled: boolean;
    targetedHunterId: integer | undefined;
}

const state: ActionModuleState = {
    isHunterBeenHit: false,
    isHunterBeenKilled: false,
    targetedHunterId: undefined
};

interface ActionExecution {
    commit: Commit;
    dispatch: Dispatch;
    target: Item | Equipment | Player | Hunter | Terminal | Planet | null;
    action: Action;
    params: Record<string, unknown>;
}


const getters: GetterTree<ActionModuleState, ActionModuleState> = {
    isHunterBeenHit: (state): boolean => {
        return state.isHunterBeenHit;
    },
    isHunterBeenKilled: (state): boolean => {
        return state.isHunterBeenKilled;
    },
    targetedHunterId: (state): integer | undefined => {
        return state.targetedHunterId;
    }
};

const actions: ActionTree<ActionModuleState, ActionModuleState> = {
    async executeAction({ commit, dispatch }, { target, action, params = {} }) {
        const actionExecution = { commit, dispatch, target, action, params };
        if (action.confirmation) {
            await dispatch("player/openConfirmPopup", { message: action.confirmation, acceptCallback: async () => { await handleActionExecution(actionExecution);} }, { root: true });
        } else {
            await handleActionExecution(actionExecution);
        }
    }

};

const mutations: MutationTree<ActionModuleState> = {
    setIsHunterBeenHit(state, isHunterBeenHit: boolean): void {
        state.isHunterBeenHit = isHunterBeenHit;
    },
    setIsHunterBeenKilled(state, isHunterBeenKilled: boolean): void {
        state.isHunterBeenKilled = isHunterBeenKilled;
    },
    setTargetedHunterId(state, targetedHunterId: integer): void {
        state.targetedHunterId = targetedHunterId;
    }
};

export const action = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};

async function handleActionExecution(actionExecution: ActionExecution): Promise<void> {
    const { commit, dispatch, target, action, params } = actionExecution;

    if (store.getters["player/isLoading"]) {
        return;
    }

    await dispatch("player/setLoading", { loading: true }, { root: true });
    await dispatch("communication/clearRoomLogs", null, { root: true });

    await ActionService.executeTargetAction(target, action, params).then(async (response: AxiosResponse<ActionResponseData>) => {
        if (action.isExchangeBodyAction()) {
            return await handleExchangeBodyAction(response, dispatch);
        }

        if (action.isShootHunterAction()) {
            handleShootHunterAction(response, commit);
        }

        await dispatch("communication/loadAlivePlayerChannels", null, { root: true });
        await dispatch("communication/changeChannel", { channel: store.getters["communication/roomChannel"] }, { root: true });
        await dispatch("player/reloadPlayer", null, { root: true });
    });
}

async function handleExchangeBodyAction(axiosResponse: AxiosResponse<ActionResponseData>, dispatch: Dispatch): Promise<void> {
    await dispatch("player/togglePlayerChanged", null, { root: true }); // avoid to load player twice
    await dispatch("player/clearPlayer", null, { root: true });
    await dispatch("auth/userInfo", null, { root: true }); // refresh user info so we get up to date player associated to the user
    await dispatch("player/loadPlayer", { playerId: axiosResponse.data.actionDetails?.playerId }, { root: true }); // load the player sent by the back-end
    await dispatch("player/togglePlayerChanged", null, { root: true });
}

function handleShootHunterAction(axiosResponse: AxiosResponse<ActionResponseData>, commit: Commit): void {
    const actionIsSuccessful = axiosResponse.data.actionResult === "success";
    const hunterIsDead = !axiosResponse.data.actionDetails?.hunterIsAlive;
    const targetedHunterId = axiosResponse.data.actionDetails?.targetedHunterId;

    // commit data to allow proper hunter animations to be played
    commit("setIsHunterBeenHit", actionIsSuccessful);
    commit("setIsHunterBeenKilled", hunterIsDead);
    commit("setTargetedHunterId", targetedHunterId);
}
