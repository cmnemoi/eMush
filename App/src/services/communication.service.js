import ApiService from "@/services/api.service";
import { Channel } from "@/entities/Channel";
import { Message } from "@/entities/Message";
import { RoomLog } from "@/entities/RoomLog";
import { PRIVATE, PUBLIC, ROOM_LOG, TIPS } from '@/enums/communication.enum';

const CHANNELS_ENDPOINT = process.env.VUE_APP_API_URL + 'channel';
const ROOM_LOGS_ENDPOINT = process.env.VUE_APP_API_URL + 'room-log';

const CommunicationService = {

    loadChannels: async() => {
        const channelsData = await ApiService.get(CHANNELS_ENDPOINT);

        let channels = [
            (new Channel()).load({ scope: TIPS, id: TIPS }),
            (new Channel()).load({ scope: ROOM_LOG, id: ROOM_LOG })
        ];
        if (channelsData.data) {
            channelsData.data.forEach((data) => {
                channels.push((new Channel()).load(data));
            });
        }
        return channels;
    },

    createPrivateChannel: async () => {
        const channelsData = await ApiService.post(CHANNELS_ENDPOINT);

        let channels = [
            (new Channel()).load({ scope: TIPS, id: TIPS }),
            (new Channel()).load({ scope: ROOM_LOG, id: ROOM_LOG })
        ];
        if (channelsData.data) {
            channelsData.data.forEach((data) => {
                channels.push((new Channel()).load(data));
            });
        }
        return channels;
    },

    loadMessages: async (channel) => {
        switch (channel.scope) {
        case PRIVATE:
        case PUBLIC:
            return loadChannelMessages();
        case ROOM_LOG:
            return loadRoomLogs();
        default:
            return [];
        }

        async function loadChannelMessages() {
            const messagesData = await ApiService.get(CHANNELS_ENDPOINT + '/' + channel.id + '/message');

            let messages = [];
            if (messagesData.data) {
                messagesData.data.forEach((data) => {
                    messages.push((new Message()).load(data));
                });
            }
            return messages;
        }

        async function loadRoomLogs() {
            const result = await ApiService.get(ROOM_LOGS_ENDPOINT);

            const logs = [];
            if (result.data) {
                const days = result.data;
                Object.keys(days).map((day) => {
                    Object.keys(days[day]).map((cycle) => {
                        let roomLogs = [];
                        days[day][cycle].forEach((value) => {
                            let roomLog = (new RoomLog()).load(value);
                            roomLogs.push(roomLog);
                        });
                        logs.push({
                            "day": day,
                            "cycle": cycle,
                            roomLogs
                        });
                    });
                });
            }
            return logs;
        }
    },

    sendMessage: async (channel, text, parent) => {

        let parentId = null;
        if (typeof parent !== "undefined") {
            parentId = parent.id;
        }

        const messagesData = await ApiService.post(CHANNELS_ENDPOINT + '/' + channel.id + '/message', {
            'message': text,
            'parent': parentId
        });

        let messages = [];
        if (messagesData.data) {
            messagesData.data.forEach((data) => {
                messages.push((new Message()).load(data));
            });
        }
        return messages;
    }
};
export default CommunicationService;
