import CommunicationService from "@/services/communication.service";
import { Channel } from "@/entities/Channel";
import { ROOM_LOG } from '@/enums/communication.enum';
import { PRIVATE, PUBLIC, TIPS } from "../enums/communication.enum";


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

const getters = {
    loading(state) {
        return state.loadingByChannelId[state.currentChannel.id] || false;
    },
    messages(state) {
        return state.messagesByChannelId[state.currentChannel.id] || [];
    },
    roomChannel(state) {
        return state.channels.find(channel => channel.scope === ROOM_LOG);
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

const actions = {
    async changeChannel({ commit, dispatch }, { channel }) {
        commit('setCurrentChannel', channel);
        dispatch('loadMessages', { channel });
    },
    async loadChannels({ getters, dispatch, commit, rootState }) {
        commit('setLoadingOfChannels', true);

        try {
            let channels = await CommunicationService.loadChannels();

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

const mutations = {
    setLoadingOfChannels(state, newStatus) {
        state.loadingChannels = newStatus;
    },

    setLoadingForChannel(state, { channel, newStatus }) {
        state.loadingByChannelId[channel.id] = newStatus;
    },

    setCurrentChannel(state, channel) {
        state.currentChannel = channel;
    },

    setChannels(state, channels) {
        state.channels = channels;
    },

    addChannel(state, channel) {
        state.channels.push(channel);
    },

    removeChannel(state, channel) {
        state.channels = state.channels.filter(({ id }) => id !== channel.id);
        delete state.loadingByChannelId[channel.id];
        delete state.messagesByChannelId[channel.id];
    },

    invitablePlayerMenu(state, { isOpen, channel }) {
        state.invitablePlayerMenuOpen = isOpen;
        state.invitationChannel = channel;
    },

    setInvitablePlayers(state, { invitablePlayers }) {
        state.invitablePlayers = invitablePlayers;
    },

    setChannelMessages(state, { channel, messages }) {
        state.messagesByChannelId[channel.id] = messages;
    }
};

export function sortChannels(channels, currentPlayerKey) {
    const channelOrderValue = {
        [TIPS] : 0,
        [ROOM_LOG] : 1,
        [PUBLIC] : 2,
        [PRIVATE] : 3
    };

    return channels.sort(function (a, b) {
        const diff = channelOrderValue[a.scope] - channelOrderValue[b.scope];

        if (diff === 0 && a.scope === PRIVATE) {
            const participantA = a.getParticipant(currentPlayerKey);
            const participantB = b.getParticipant(currentPlayerKey);

            return (participantA.joinedAt > participantB.joinedAt);
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
