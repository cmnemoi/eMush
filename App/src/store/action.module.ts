import ActionService from "@/services/action.service";
import { ActionTree, Commit, Dispatch, GetterTree, MutationTree } from "vuex";
import store from "@/store/index";
import { ActionEnum, ShootHunterActionsEnum } from "@/enums/action.enum";
import { AxiosResponse } from "axios";
import { Action } from "@/entities/Action";
import { Hunter } from "@/entities/Hunter";
import { Terminal } from "@/entities/Terminal";
import { Planet } from "@/entities/Planet";
import { Item } from "@/entities/Item";
import { Player } from "@/entities/Player";
import { Equipment } from "@/entities/Equipment";

const state = {
    isHunterBeenHit: false,
    isHunterBeenKilled: false,
    targetedHunterId: undefined
};

interface ActionExecution {
    commit: Commit;
    dispatch: Dispatch;
    target: Item | Equipment | Player | Hunter | Terminal | Planet | null;
    action: Action;
    params: any;
}


const getters: GetterTree<any, any> = {
    isHunterBeenHit: (state: any): boolean => {
        return state.isHunterBeenHit;
    },
    isHunterBeenKilled: (state: any): boolean => {
        return state.isHunterBeenKilled;
    },
    targetedHunterId: (state: any): integer | undefined => {
        return state.targetedHunterId;
    }
};

const actions: ActionTree<any, any> = {
    async executeAction({ commit, dispatch }, { target, action, params = {} }) {
        const actionExecution = { commit, dispatch, target, action, params };
        if (action.confirmation) {
            await dispatch("player/openConfirmPopup", { message: action.confirmation, acceptCallback: async () => { await handleActionExecution(actionExecution);} }, { root: true });
        } else {
            await handleActionExecution(actionExecution);
        }
    }

};

const mutations: MutationTree<any> = {
    setIsHunterBeenHit(state: any, isHunterBeenHit: boolean): void {
        state.isHunterBeenHit = isHunterBeenHit;
    },
    setIsHunterBeenKilled(state: any, isHunterBeenKilled: boolean): void {
        state.isHunterBeenKilled = isHunterBeenKilled;
    },
    setTargetedHunterId(state: any, targetedHunterId: integer): void {
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

    await ActionService.executeTargetAction(target, action, params).then(async (response: AxiosResponse<any, any>) => {
        if (isShootAction(action)) {
            handleShootHunterAction(response, commit);
        }
        if (isExchangeBodyAction(action)) {
            await handleExchangeBodyAction(response, dispatch);
        } else {
            await dispatch("player/reloadPlayer", null, { root: true });
        }
        await dispatch("communication/changeChannel", { channel: store.getters["communication/roomChannel"] }, { root: true });
    });
}

async function handleExchangeBodyAction(axiosResponse: AxiosResponse<any, any>, dispatch: Dispatch): Promise<void> {
    await dispatch("player/togglePlayerChanged", null, { root: true }); // avoid to load player twice
    await dispatch("player/clearPlayer", null, { root: true });
    await dispatch("auth/userInfo", null, { root: true }); // refresh user info so we get up to date player associated to the user
    await dispatch("player/loadPlayer", { playerId: axiosResponse.data.actionDetails.playerId }, { root: true }); // load the player sent by the back-end
    await dispatch("player/togglePlayerChanged", null, { root: true });
}

function handleShootHunterAction(axiosResponse: AxiosResponse<any, any>, commit: Commit): void {
    const actionIsSuccessful = axiosResponse.data.actionResult === "success";
    const hunterIsDead = !axiosResponse.data.actionDetails.hunterIsAlive;
    const targetedHunterId = axiosResponse.data.actionDetails.targetedHunterId;

    // commit data to allow proper hunter animations to be played
    commit("setIsHunterBeenHit", actionIsSuccessful);
    commit("setIsHunterBeenKilled", hunterIsDead);
    commit("setTargetedHunterId", targetedHunterId);
}

function isShootAction(action: Action): boolean {
    return Object.values(ShootHunterActionsEnum).includes(action.key as ShootHunterActionsEnum);
}

function isExchangeBodyAction(action: Action): boolean {
    return action.key === ActionEnum.EXCHANGE_BODY;
}
