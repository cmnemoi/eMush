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
    playerChanged: false
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
    async loadPlayer({ commit, dispatch }, { playerId }) {
        commit('setLoading', true);
        try {
            const isNewGame = store.getters['player/player'] === null;
            const player = store.getters['player/player'];
            await Promise.all([
                isNewGame ? Promise.resolve() : this.dispatch("daedalus/loadAlerts", { daedalus: player.daedalus }),
                isNewGame ? Promise.resolve() : this.dispatch("daedalus/loadMinimap", { player }),
                PlayerService.loadPlayer(playerId).then(async (player: Player | null) => {
                    commit('updatePlayer', player);
                    if (player?.gameStatus !== 'in_game') {
                        return true;
                    }
                    await Promise.all([
                        isNewGame ? this.dispatch("daedalus/loadAlerts", { daedalus: player.daedalus }) : Promise.resolve(),
                        isNewGame ? this.dispatch("daedalus/loadMinimap", { player }) : Promise.resolve(),
                        this.dispatch("room/loadRoom", { room: player?.room }),
                        this.dispatch("room/updateSelectedItemPile"),
                        player?.spaceBattle !== null ? this.dispatch("room/loadSpaceBattle", { spaceBattle: player?.spaceBattle }) : null
                    ]);
                    commit('updateSelectedItem');
                })
            ]);
            return true;
        } catch (e) {
            // an error here probably means player in store is not the expected player : case of transfer.
            // so we re try by refreshing user info
            try {
                console.error(e);
                await dispatch("player/togglePlayerChanged", null, { root: true }); // avoid to load player twice
                await dispatch("player/clearPlayer", null, { root: true });
                await dispatch("error/clearError", null, { root: true });
                const user: User = await dispatch("auth/userInfo", null, { root: true });
                await dispatch("loadPlayer", { playerId: user.playerInfo });
                await dispatch("player/togglePlayerChanged", null, { root: true });
            }
            // bad luck, then throw the error
            catch (e) {
                console.error(e);
                commit('errorUpdatePlayer');
                return false;
            }
        } finally {
            await dispatch("popup/openPlayerNotificationPopUp", { player: store.getters["player/player"] }, { root: true });
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
        commit('setDisplayMushSkills', player.isMush());
    },
    toggleMushSkillsDisplay({ commit }) {
        commit('toggleMushSkillsDisplay');
    },
    togglePlayerChanged({ commit }) {
        commit('setPlayerChanged', !state.playerChanged);
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
    }
};

export const player = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
