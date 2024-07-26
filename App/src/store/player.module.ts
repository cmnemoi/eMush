import PlayerService from "@/services/player.service";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import { Player } from "@/entities/Player";
import { Item } from "@/entities/Item";
import { ConfirmPopup } from "@/entities/ConfirmPopup";

const state =  {
    loading: false,
    player: null,
    selectedItem: null,
    confirmPopup: new ConfirmPopup(),
    displayMushSkills: false
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
    }
};

const actions: ActionTree<any, any> = {
    storePlayer({ commit }, { player }) {
        commit('updatePlayer', player);
    },
    async loadPlayer({ commit }, { playerId }) {
        commit('setLoading', true);
        try {
            await Promise.all([
                this.dispatch("daedalus/loadAlerts"),
                this.dispatch("daedalus/loadMinimap"),
                PlayerService.loadPlayer(playerId).then(async (player: Player | null) => {
                    commit('updatePlayer', player);
                    if (player?.gameStatus !== 'in_game') {
                        return true;
                    }
                    await Promise.all([
                        this.dispatch("room/loadRoom", { room: player?.room }),
                        this.dispatch("room/updateSelectedItemPile"),
                        player?.spaceBattle !== null ? this.dispatch("room/loadSpaceBattle", { spaceBattle: player?.spaceBattle }) : null
                    ]);
                    commit('updateSelectedItem');
                })
            ]);
            return true;
        } catch (e) {
            console.error(e);
            commit('errorUpdatePlayer');
            return false;
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
    initMushSkillsDisplay({ commit }, { player }) {
        commit('setDisplayMushSkills', player.isMush());
    },
    toggleMushSkillsDisplay({ commit }) {
        commit('toggleMushSkillsDisplay');
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
    }
};

export const player = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
