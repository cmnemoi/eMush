import CommunicationService from "@/services/communication.service";
import { Channel } from "@/entities/Channel";
import { ActionTree, GetterTree, MutationTree } from "vuex";
import { ChannelType } from "@/enums/communication.enum";


const state =  {
    currentChannel: new Channel(),
    invitablePlayerMenuOpen: false,
    invitablePlayers: [],
    invitationChannel: null,
    channels: [],
    loadingChannels: false,
    loadingByChannelId: {},
    messagesByChannelId: {}
};

const getters: GetterTree<any, any> = {
    loading(state) {
        return state.loadingByChannelId[state.currentChannel.id] || false;
    },
    messages(state) {
        return state.messagesByChannelId[state.currentChannel.id] || [];
    },
    roomChannel(state) {
        return state.channels.find((channel: Channel) => channel.scope === ChannelType.ROOM_LOG);
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

const actions: ActionTree<any, any> = {
    async changeChannel({ commit, dispatch, state }, { channel }) {
        if (state.loadingChannels) { return; }

        try {
            if (channel.scope === ChannelType.NEW_CHANNEL) {
                const newChannel = await CommunicationService.createPrivateChannel();
                commit('setCurrentChannel', newChannel);
                await dispatch('loadChannels');
            } else {
                commit('setCurrentChannel', channel);
                await dispatch('loadMessages', { channel });
            }
        } catch (e) {
            return false;
        }
    },
    async loadChannels({ getters, dispatch, commit, rootState }) {
        commit('setLoadingOfChannels', true);

        try {
            const channels = await CommunicationService.loadChannels();

            const currentPlayerKey = rootState.player.player.character.key;
            const sortedChannels = sortChannels(channels, currentPlayerKey);

            commit('setChannels', sortedChannels);

            if (state.currentChannel.scope === undefined) {
                commit('setCurrentChannel', getters.roomChannel);
            }

            await dispatch('loadMessages', { channel: state.currentChannel });
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
    async leavePrivateChannel({ getters, commit, dispatch }, channel) {
        commit('setLoadingOfChannels', true);
        try {
            await CommunicationService.leaveChannel(channel);
            commit('removeChannel', channel);
            commit('setCurrentChannel', getters.roomChannel);
            await dispatch('loadChannels');
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

const mutations: MutationTree<any> = {
    setLoadingOfChannels(state: any, newStatus: string): void {
        state.loadingChannels = newStatus;
    },

    setLoadingForChannel(state: any, { channel, newStatus }): void {
        state.loadingByChannelId[channel.id] = newStatus;
    },

    setCurrentChannel(state: any, channel: Channel): void {
        state.currentChannel = channel;
    },

    setChannels(state: any, channels: Channel[]): void {
        state.channels = channels;
    },

    removeChannel(state: any, channel: Channel): void {
        state.channels = state.channels.filter(({ id }: {id: number}) => id !== channel.id);
        delete state.loadingByChannelId[channel.id];
        delete state.messagesByChannelId[channel.id];
    },

    invitablePlayerMenu(state: any, { isOpen, channel }): void {
        state.invitablePlayerMenuOpen = isOpen;
        state.invitationChannel = channel;
    },

    setInvitablePlayers(state: any, { invitablePlayers }): void {
        state.invitablePlayers = invitablePlayers;
    },

    setChannelMessages(state: any, { channel, messages }): void {
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
        [ChannelType.PRIVATE] : 5,
        [ChannelType.NEW_CHANNEL] : 6
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
