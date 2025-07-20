import PlayerService from "@/services/player.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import { Player } from "@/entities/Player";
import { Item } from "@/entities/Item";
import { ConfirmPopup } from "@/entities/ConfirmPopup";
import store from ".";
import { User } from "@/entities/User";

const state =  {
    loading: false,
    player: null,
    selectedItem: null,
    confirmPopup: new ConfirmPopup(),
    displayMushSkills: false,
    playerChanged: false,
    commanderOrderPanelOpen: false,
    comManagerAnnouncementPanelOpen: false
};

const getters: GetterTree<any, any> = {
    isLoading: (state: any): boolean => {
        return state.loading;
    },
    player: (state: any): Player|null => {
        return state.player;
    },
    selectedItem: (state: any): Item|null => {
        return state.selectedItem;
    },
    confirmPopup: (state: any): ConfirmPopup => {
        return state.confirmPopup;
    },
    displayMushSkills: (state: any): boolean => {
        return state.displayMushSkills;
    },
    playerChanged: (state: any): boolean => {
        return state.playerChanged;
    },
    commanderOrderPanelOpen: (state: any): boolean => {
        return state.commanderOrderPanelOpen;
    },
    comManagerAnnouncementPanelOpen: (state: any): boolean => {
        return state.comManagerAnnouncementPanelOpen;
    }
};

const actions: ActionTree<any, any> = {
    storePlayer({ commit }, { player }) {
        commit('updatePlayer', player);
    },
    async chooseSkill({ commit }, { player, skill }) {
        commit('setLoading', true);
        try {
            await PlayerService.chooseSkill(player, skill);
            await this.dispatch('popup/closeSkillSelectionPopUp');
            await this.dispatch('player/reloadPlayer');
        } catch (error) {
            console.error(error);
        }
        commit('setLoading', false);
    },
    async deleteNotification({ commit }) {
        commit('setLoading', true);
        try {
            const player = store.getters['player/player'];
            await PlayerService.deleteNotification(player);
        } catch (error) {
            console.error(error);
        }
        commit('setLoading', false);
    },
    async toggleMissionCompletion({ commit }, { mission }) {
        commit('setLoading', true);
        try {
            await PlayerService.toggleMissionCompletion(mission.id);
        } catch (error) {
            console.error(error);
        }
        commit('setLoading', false);
    },
    async loadPlayer({ commit, dispatch, state }, { playerId, isRetry = false }) {
        if (!playerId) {
            commit('clearPlayer');
            return;
        }

        commit('setLoading', true);
        try {
            const result = await executePlayerLoad(dispatch, commit, playerId);
            return result;
        } catch (e) {
            return await handlePlayerLoadError(dispatch, commit, e, isRetry, playerId, state);
        } finally {
            await finalizePlayerLoad(dispatch, commit);
        }
    },
    async reloadPlayer({ state, dispatch }) {
        return await dispatch("loadPlayer", { playerId: state.player.id });
    },
    async clearPlayer({ commit }) {
        commit("clearPlayer");
        await this.dispatch("room/clearRoom");
        await this.dispatch("daedalus/clearDaedalus");
        await this.dispatch("communication/clearRoomLogs");
        await this.dispatch("communication/clearChannels");
    },
    setLoading({ commit }, { loading }) {
        commit('setLoading', loading);
    },
    selectTarget({ commit }, { target }) {
        commit('setSelectedItem', target);
    },
    openConfirmPopup({ commit }, { message, acceptCallback, refuseCallback = () => {} }) {
        commit('openConfirmPopup', { message, acceptCallback, refuseCallback });
    },
    closeConfirmPopup({ commit }) {
        commit('closeConfirmPopup');
    },
    acceptConfirmPopup({ commit }) {
        commit('acceptConfirmPopup');
    },
    refuseConfirmPopup({ commit }) {
        commit('refuseConfirmPopup');
    },

    initMushSkillsDisplay({ commit }, { player }) {
        commit('setDisplayMushSkills', player.isMush);
    },
    toggleMushSkillsDisplay({ commit }) {
        commit('toggleMushSkillsDisplay');
    },
    togglePlayerChanged({ commit }) {
        commit('setPlayerChanged', !state.playerChanged);
    },
    openCommanderOrderPanel({ commit }) {
        commit('openCommanderOrderPanel');
    },
    closeCommanderOrderPanel({ commit }) {
        commit('closeCommanderOrderPanel');
    },
    openComManagerAnnouncementPanel({ commit }) {
        commit('openComManagerAnnouncementPanel');
    },
    closeComManagerAnnouncementPanel({ commit }) {
        commit('closeComManagerAnnouncementPanel');
    }
};

const mutations : MutationTree<any> = {
    setLoading(state: any, newValue: boolean): void {
        state.loading = newValue;
    },
    updatePlayer(state: any, player: Player): void {
        state.player = player;
        state.loading = false;
    },
    clearPlayer(state: any): void
    {
        state.player = null;
    },
    errorUpdatePlayer(state: any): void {
        state.loading = false;
    },
    setSelectedItem(state, target: Item | null) {
        state.selectedItem = target;
    },
    updateSelectedItem(state) {
        const oldTarget = state.selectedItem;

        const targetList = (<Player>state.player).items;
        if (oldTarget !== null) {
            for (let i = 0; i < targetList.length; i++) {
                const target = targetList[i];
                if (oldTarget.id === target.id) {
                    return state.selectedItem = target;
                }
            }
            return state.selectedItem = null;
        }
    },
    acceptConfirmPopup(state) {
        state.confirmPopup.isOpen = false;
        state.confirmPopup.acceptCallback();

        state.confirmPopup = new ConfirmPopup();
    },
    refuseConfirmPopup(state) {
        state.confirmPopup.isOpen = false;
        state.confirmPopup.refuseCallback();

        state.confirmPopup = new ConfirmPopup();
    },
    openConfirmPopup(state, { message, acceptCallback, refuseCallback }) {
        state.confirmPopup.isOpen = true;
        state.confirmPopup.message = message;
        state.confirmPopup.acceptCallback = acceptCallback;
        state.confirmPopup.refuseCallback = refuseCallback;
    },
    closeConfirmPopup(state) {
        state.confirmPopup = new ConfirmPopup();
    },
    setDisplayMushSkills(state, display: boolean) {
        state.displayMushSkills = display;
    },
    toggleMushSkillsDisplay(state) {
        state.displayMushSkills = !state.displayMushSkills;
    },
    setPlayerChanged(state, playerChanged: boolean) {
        state.playerChanged = playerChanged;
    },
    openCommanderOrderPanel(state) {
        state.commanderOrderPanelOpen = true;
    },
    closeCommanderOrderPanel(state) {
        state.commanderOrderPanelOpen = false;
    },
    openComManagerAnnouncementPanel(state) {
        state.comManagerAnnouncementPanelOpen = true;
    },
    closeComManagerAnnouncementPanel(state) {
        state.comManagerAnnouncementPanelOpen = false;
    }
};

export const player = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};

async function executePlayerLoad(dispatch: any, commit: any, playerId: number): Promise<boolean> {
    const playerIsNull = store.getters['player/player'] === null;
    const currentPlayer = store.getters['player/player'];

    await Promise.all([
        loadAlertsForCurrentPlayer(dispatch, playerIsNull, currentPlayer),
        loadPlayerData(dispatch, commit, playerId, playerIsNull)
    ]);
    return true;
}

async function loadAlertsForCurrentPlayer(dispatch: any, playerIsNull: boolean, currentPlayer: Player): Promise<void> {
    if (playerIsNull) {
        return Promise.resolve();
    }
    if (!currentPlayer?.isAlive()) {
        return Promise.resolve();
    }
    return dispatch("daedalus/loadAlerts", { daedalus: currentPlayer.daedalus }, { root: true });
}

async function loadAlertsIfNeeded(dispatch: any, player: Player | null, playerIsNull: boolean): Promise<void> {
    console.log('loadAlertsIfNeeded - playerIsNull:', playerIsNull, 'player:', player);
    if (!playerIsNull) {
        return Promise.resolve();
    }
    if (!player?.isAlive()) {
        return Promise.resolve();
    }
    return dispatch("daedalus/loadAlerts", { daedalus: player.daedalus }, { root: true });
}

async function loadPlayerData(dispatch: any, commit: any, playerId: number, playerIsNull: boolean): Promise<void> {
    const player = await PlayerService.loadPlayer(playerId);
    if (player === null) {
        return;
    }
    validatePlayerMatch(player, playerId);
    commit('updatePlayer', player);

    if (player?.gameStatus !== 'in_game') {
        return;
    }

    await loadPlayerDependencies(dispatch, player, playerIsNull);
    await loadOptionalPlayerData(dispatch, player);
    await updatePlayerInterface(dispatch, commit);
}

function validatePlayerMatch(player: Player | null, playerId: number): void {
    if (player?.id !== playerId) {
        throw new Error(`Player mismatch: expected ${playerId}, got ${player?.id}`);
    }
}

async function loadPlayerDependencies(dispatch: any, player: Player | null, playerIsNull: boolean): Promise<void> {
    await Promise.all([
        loadAlertsIfNeeded(dispatch, player, playerIsNull),
        dispatch("daedalus/loadMinimap", { player }, { root: true }),
        dispatch("room/loadRoom", { room: player?.room }, { root: true })
    ]);
}

async function loadOptionalPlayerData(dispatch: any, player: Player | null): Promise<void> {
    if (player?.spaceBattle) {
        await dispatch("room/loadSpaceBattle", { spaceBattle: player?.spaceBattle }, { root: true });
    }
}

async function updatePlayerInterface(dispatch: any, commit: any): Promise<void> {
    await dispatch("room/updateSelectedItemPile", null, { root: true });
    commit('updateSelectedItem');
}

async function handlePlayerLoadError(dispatch: any, commit: any, e: any, isRetry: boolean, playerId: number, state: any): Promise<boolean> {
    if (shouldRetryPlayerLoad(isRetry, state)) {
        return await retryPlayerLoad(dispatch, commit, playerId);
    }

    console.error('Load player failed (no retry):', e);
    commit('errorUpdatePlayer');
    return false;
}

function shouldRetryPlayerLoad(isRetry: boolean, state: any): boolean {
    return !isRetry && !state.playerChanged;
}

async function retryPlayerLoad(dispatch: any, commit: any, playerId: number): Promise<boolean> {
    try {
        console.warn('Player load failed, attempting retry with fresh user info');
        await preparePlayerRetry(dispatch);
        const user: User = await dispatch("auth/userInfo", null, { root: true });
        const result = await dispatch("loadPlayer", { playerId: user.playerInfo, isRetry: true });
        await dispatch("player/togglePlayerChanged", null, { root: true });
        return result;
    } catch (retryError) {
        console.error('Retry failed:', retryError);
        commit('errorUpdatePlayer');
        return false;
    }
}

async function preparePlayerRetry(dispatch: any): Promise<void> {
    await dispatch("player/togglePlayerChanged", null, { root: true });
    await dispatch("player/clearPlayer", null, { root: true });
    await dispatch("error/clearError", null, { root: true });
}

async function finalizePlayerLoad(dispatch: any, commit: any): Promise<void> {
    await dispatch("popup/openPlayerNotificationPopUp", { player: store.getters["player/player"] }, { root: true });
    commit('setLoading', false);
}
