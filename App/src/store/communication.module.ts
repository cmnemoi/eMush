import CommunicationService from "@/services/communication.service";
import { Channel } from "@/entities/Channel";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import { ChannelType } from "@/enums/communication.enum";
import { Player } from "@/entities/Player";

interface StoreState {
    currentChannel: Channel;
    invitablePlayerMenuOpen: boolean,
    invitablePlayers: Array<Player> | null,
    invitationChannel: null,
    channels?: Array<Channel> | null,
    loadingChannels: boolean,
    loadingByChannelId: Array<number>,
    messagesByChannelId: Record<string, unknown>
};

const state =  {
    currentChannel: new Channel(),
    invitablePlayerMenuOpen: false,
    invitablePlayers: null,
    invitationChannel: null,
    channels: null,
    loadingChannels: false,
    loadingByChannelId: {},
    messagesByChannelId: {}
} as StoreState;

const getters: GetterTree<StoreState, StoreState> = {
    loading(state) {
        return state.loadingByChannelId[state.currentChannel.id] || false;
    },
    messages(state) {
        return state.messagesByChannelId[state.currentChannel.id] || [];
    },
    roomChannel(state) {
        return state.channels?.find((channel: Channel) => channel.scope === ChannelType.ROOM_LOG);
    },
    invitablePlayerMenuOpen(state) {
        return state.invitablePlayerMenuOpen;
    },
    invitablePlayers(state) {
        return state.invitablePlayers;
    },
    invitationChannel(state) {
        return state.invitationChannel;
    },
    channels(state) {
        return state.channels;
    }
};

const actions: ActionTree<StoreState, any> = {
    async changeChannel({ commit, dispatch }, { channel }) {
        commit('setCurrentChannel', channel);
        dispatch('loadMessages', { channel });
    },
    async loadChannels({ getters, dispatch, commit, rootState }) {
        commit('setLoadingOfChannels', true);

        try {
            const channels = await CommunicationService.loadChannels();

            const currentPlayerKey = rootState.player.player.characterKey;
            const sortedChannels = sortChannels(channels, currentPlayerKey);

            commit('setChannels', sortedChannels);
            commit('setCurrentChannel', getters.roomChannel);
            dispatch('loadMessages', { channel: getters.roomChannel });
            commit('setLoadingOfChannels', false);
            return true;
        } catch (e) {
            commit('setLoadingOfChannels', false);
            return false;
        }
    },

    async loadMessages({ commit }, { channel }) {
        commit('setLoadingForChannel', { channel, newStatus: true });
        try {
            const messages = await CommunicationService.loadMessages(channel);
            commit('setChannelMessages', { channel, messages });
            commit('setLoadingForChannel', { channel, newStatus: false });
            return true;
        } catch (e) {
            commit('setLoadingForChannel', { channel, newStatus: false });
            return false;
        }
    },

    async sendMessage({ commit }, { channel, text, parent }) {
        commit('setLoadingForChannel', { channel, newStatus: true });

        try {
            const messages = await CommunicationService.sendMessage(channel, text, parent);
            commit('setChannelMessages', { channel, messages });
            commit('setLoadingForChannel', { channel, newStatus: false });
            return true;
        } catch (e) {
            commit('setLoadingForChannel', { channel, newStatus: false });
            return false;
        }
    },

    async createPrivateChannel({ state, commit }) {
        if (state.loadingChannels) { return; }
        commit('setLoadingOfChannels', true);

        try {
            const newChannel = await CommunicationService.createPrivateChannel();
            commit('addChannel', newChannel);
            commit('setCurrentChannel', newChannel);
            commit('setLoadingOfChannels', false);
        } catch (e) {
            commit('setLoadingOfChannels', false);
        }
    },

    async leavePrivateChannel({ getters, commit }, channel) {
        commit('setLoadingOfChannels', true);
        try {
            await CommunicationService.leaveChannel(channel);
            commit('removeChannel', channel);
            commit('setCurrentChannel', getters.roomChannel);
            commit('setLoadingOfChannels', false);
        } catch (e) {
            commit('setLoadingOfChannels', false);
        }
    },

    async getInvitablePlayersToPrivateChannel({ commit }, channel) {
        commit('invitablePlayerMenu', { isOpen: true, channel: channel });
        commit("player/setLoading", true, { root: true });
        const invitablePlayers = await CommunicationService.loadInvitablePlayers(channel);
        commit("player/setLoading", false, { root: true });
        commit('setInvitablePlayers', { invitablePlayers: invitablePlayers });
    },

    async invitePlayer({ dispatch }, { player, channel }) {
        await CommunicationService.invitePlayer(player, channel);
        await dispatch('closeInvitation');
        await dispatch('loadChannels');
    },

    async closeInvitation({ commit }) {
        commit('setInvitablePlayers', { invitablePlayers: [] });
        commit('invitablePlayerMenu', { isOpen: false, channel: null });
    },

    clearRoomLogs({ getters, commit }) {
        commit('setChannelMessages', { channel: getters.roomChannel, messages: [] });
    },
    async loadRoomLogs({ getters, dispatch }) {
        await dispatch('loadMessages', { channel: getters.roomChannel });
    }
};

const mutations: MutationTree<StoreState> = {
    setLoadingOfChannels(state, newStatus: boolean): void {
        state.loadingChannels = newStatus;
    },

    setLoadingForChannel(state, { channel, newStatus }): void {
        state.loadingByChannelId[channel.id] = newStatus;
    },

    setCurrentChannel(state, channel: Channel): void {
        state.currentChannel = channel;
    },

    setChannels(state, channels: Channel[]): void {
        state.channels = channels;
    },

    addChannel(state, channel: Channel): void {
        state.channels?.push(channel);
    },

    removeChannel(state, channel: Channel): void {
        state.channels = state.channels?.filter(({ id }: {id: number}) => id !== channel.id);
        delete state.loadingByChannelId[channel.id];
        delete state.messagesByChannelId[channel.id];
    },

    invitablePlayerMenu(state, { isOpen, channel }): void {
        state.invitablePlayerMenuOpen = isOpen;
        state.invitationChannel = channel;
    },

    setInvitablePlayers(state, { invitablePlayers }): void {
        state.invitablePlayers = invitablePlayers;
    },

    setChannelMessages(state, { channel, messages }): void {
        state.messagesByChannelId[channel.id] = messages;
    }
};

export function sortChannels(channels: Channel[], currentPlayerKey: string): Channel[] {
    const channelOrderValue = {
        [ChannelType.TIPS] : 0,
        [ChannelType.FAVORITES] : 1,
        [ChannelType.MUSH] : 2,
        [ChannelType.ROOM_LOG] : 3,
        [ChannelType.PUBLIC] : 4,
        [ChannelType.PRIVATE] : 5
    };

    return channels.sort(function (a: Channel, b: Channel) : number {
        const diff = channelOrderValue[a.scope] - channelOrderValue[b.scope];

        if (diff === 0 && a.scope === ChannelType.PRIVATE) {
            const participantA = a.getParticipant(currentPlayerKey);
            const participantB = b.getParticipant(currentPlayerKey);

            if (typeof participantA === "undefined" || typeof participantB === "undefined") {
                console.error(participantA, participantB, 'is undefined');
                return 0;
            }

            return (participantA.joinedAt > participantB.joinedAt) ? 1 : -1;
        }

        return diff;
    });
}

export const communication = {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
